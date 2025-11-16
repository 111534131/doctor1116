using Microsoft.EntityFrameworkCore;
using Backend.Data;
using Backend.Models;
using Microsoft.AspNetCore.Authentication.JwtBearer;
using Microsoft.IdentityModel.Tokens;
using System.Text;
using System.Security.Claims;
using System.IdentityModel.Tokens.Jwt;
using Google.Apis.Auth;
using Microsoft.AspNetCore.Authorization;
using Backend;

var builder = WebApplication.CreateBuilder(args);

// Add services to the container.
builder.Services.AddDbContext<DoctorDbContext>(options =>
{
    var connectionString = builder.Configuration.GetConnectionString("DefaultConnection");
    options.UseMySql(connectionString, ServerVersion.AutoDetect(connectionString));
});

// Configure JWT Authentication
var jwtKey = builder.Configuration["Jwt:Key"] ?? throw new InvalidOperationException("JWT Key not found.");
var jwtIssuer = builder.Configuration["Jwt:Issuer"] ?? throw new InvalidOperationException("JWT Issuer not found.");
var jwtAudience = builder.Configuration["Jwt:Audience"] ?? throw new InvalidOperationException("JWT Audience not found.");

builder.Services.AddAuthentication(options =>
{
    options.DefaultAuthenticateScheme = JwtBearerDefaults.AuthenticationScheme;
    options.DefaultChallengeScheme = JwtBearerDefaults.AuthenticationScheme;
})
.AddJwtBearer(options =>
{
    options.TokenValidationParameters = new TokenValidationParameters
    {
        ValidateIssuer = true,
        ValidateAudience = true,
        ValidateLifetime = true,
        ValidateIssuerSigningKey = true,
        ValidIssuer = jwtIssuer,
        ValidAudience = jwtAudience,
        IssuerSigningKey = new SymmetricSecurityKey(Encoding.UTF8.GetBytes(jwtKey))
    };
});

builder.Services.AddAuthorization(options =>
{
    options.AddPolicy("Admin", policy => policy.RequireRole("Admin"));
    options.AddPolicy("Doctor", policy => policy.RequireRole("Admin", "Doctor"));
});


builder.Services.AddEndpointsApiExplorer();
builder.Services.AddSwaggerGen();

builder.Services.AddCors(options =>
{
    options.AddPolicy("AllowAnyOrigin",
        builder => builder.AllowAnyOrigin()
                          .AllowAnyMethod()
                          .AllowAnyHeader());
});

// Configure JSON serialization to handle cycles
builder.Services.Configure<Microsoft.AspNetCore.Http.Json.JsonOptions>(options =>
{
    options.SerializerOptions.ReferenceHandler = System.Text.Json.Serialization.ReferenceHandler.IgnoreCycles;
});


var app = builder.Build();

// Seed the database with initial users
await Helpers.SeedData(app);

if (app.Environment.IsDevelopment())
{
    app.UseSwagger();
    app.UseSwaggerUI();
}

app.Use(async (context, next) =>
{
    context.Response.Headers.Append("Cross-Origin-Opener-Policy", "same-origin-allow-popups");
    await next();
});

app.UseCors("AllowAnyOrigin");

app.UseAuthentication();
app.UseAuthorization();

// --- Auth Endpoints ---
app.MapPost("/api/auth/register", async (UserRegistrationDto userDto, DoctorDbContext db) =>
{
    if (await db.Users.AnyAsync(u => u.Email == userDto.Email))
    {
        return Results.Conflict("User with this email already exists.");
    }

    var user = new User
    {
        Username = userDto.Username,
        Email = userDto.Email,
        PasswordHash = BCrypt.Net.BCrypt.HashPassword(userDto.Password),
        Role = "User" // Default role
    };

    db.Users.Add(user);
    await db.SaveChangesAsync();

    // Also create a patient record for the new user
    var patient = new Patient
    {
        UserId = user.Id,
        Name = user.Username,
        ContactInfo = user.Email,
        DateOfBirth = DateTime.UtcNow.AddYears(-30) // Placeholder DOB
    };
    db.Patients.Add(patient);
    await db.SaveChangesAsync();

    return Results.Ok(new { message = "User registered successfully" });
});

app.MapPost("/api/auth/login", async (UserLoginDto userDto, DoctorDbContext db) =>
{
    var user = await db.Users.FirstOrDefaultAsync(u => u.Email == userDto.Email);

    if (user == null || !BCrypt.Net.BCrypt.Verify(userDto.Password, user.PasswordHash))
    {
        return Results.Unauthorized();
    }

    // Ensure a patient record exists for the user
    var patient = await db.Patients.FirstOrDefaultAsync(p => p.UserId == user.Id);
    if (patient == null)
    {
        patient = new Patient
        {
            UserId = user.Id,
            Name = user.Username,
            ContactInfo = user.Email,
            DateOfBirth = DateTime.UtcNow.AddYears(-30) // Placeholder DOB
        };
        db.Patients.Add(patient);
        await db.SaveChangesAsync();
    }

    var token = Helpers.GenerateJwtToken(user, jwtKey, jwtIssuer, jwtAudience);
    return Results.Ok(new { token });
});

app.MapPost("/api/auth/google-login", async (GoogleLoginRequest request, DoctorDbContext db) =>
{
    var googleClientId = builder.Configuration["Google:ClientId"];
    var settings = new GoogleJsonWebSignature.ValidationSettings()
    {
        Audience = new List<string> { googleClientId ?? throw new InvalidOperationException("Google ClientId not configured.") }
    };

    try
    {
        var payload = await GoogleJsonWebSignature.ValidateAsync(request.Credential, settings);

        var user = await db.Users.FirstOrDefaultAsync(u => u.GoogleId == payload.Subject);

        if (user == null) // User not found, create a new one
        {
            user = await db.Users.FirstOrDefaultAsync(u => u.Email == payload.Email);
            if (user != null)
            {
                // Link existing email user to Google account
                user.GoogleId = payload.Subject;
            }
            else
            {
                // Create a brand new user
                user = new User
                {
                    Email = payload.Email,
                    Username = payload.Name,
                    GoogleId = payload.Subject,
                    Role = "User" // Default role
                };
                db.Users.Add(user);
            }
             await db.SaveChangesAsync();

            // Ensure a patient record exists for the user
            var patient = await db.Patients.FirstOrDefaultAsync(p => p.UserId == user.Id);
            if (patient == null)
            {
                patient = new Patient
                {
                    UserId = user.Id,
                    Name = user.Username,
                    ContactInfo = user.Email,
                    DateOfBirth = DateTime.UtcNow.AddYears(-30) // Placeholder DOB
                };
                db.Patients.Add(patient);
                await db.SaveChangesAsync();
            }
        }

        var token = Helpers.GenerateJwtToken(user, jwtKey, jwtIssuer, jwtAudience);
        return Results.Ok(new { token });
    }
    catch (InvalidJwtException)
    {
        return Results.Unauthorized();
    }
});


// --- Admin Endpoints ---
app.MapGet("/api/users", async (DoctorDbContext db) =>
{
    return await db.Users.Select(u => new { u.Id, u.Username, u.Email, u.Role }).ToListAsync();
}).RequireAuthorization("Admin");

app.MapPut("/api/users/{id}/role", async (int id, UpdateRoleRequest request, DoctorDbContext db) =>
{
    var user = await db.Users.FindAsync(id);
    if (user == null)
    {
        return Results.NotFound("User not found.");
    }

    user.Role = request.Role;
    await db.SaveChangesAsync();

    return Results.Ok(new { message = "User role updated successfully." });
}).RequireAuthorization("Admin");


// --- Existing Endpoints (now with Authorization) ---

app.MapGet("/api/doctors", async (DoctorDbContext db) =>
{
    return await db.Doctors.ToListAsync();
});

app.MapGet("/api/doctors/user/{userId}", async (int userId, DoctorDbContext db) =>
{
    var doctor = await db.Doctors
        .Where(d => d.UserId == userId)
        .Select(d => new { d.Id, d.Name, d.Specialty, d.UserId }) // Project to an anonymous object
        .FirstOrDefaultAsync();

    if (doctor == null)
    {
        return Results.NotFound("Doctor not found for this user.");
    }
    return Results.Ok(doctor);
}).RequireAuthorization("Doctor");

app.MapGet("/api/doctors/me/settings", async (HttpContext context, DoctorDbContext db) =>
{
    try
    {
        var userIdClaim = context.User.FindFirst(ClaimTypes.NameIdentifier);
        if (userIdClaim == null || !int.TryParse(userIdClaim.Value, out var userId))
        {
            return Results.Unauthorized();
        }

        var doctor = await db.Doctors.AsNoTracking().FirstOrDefaultAsync(d => d.UserId == userId);
        if (doctor == null)
        {
            return Results.NotFound("Doctor record not found for this user.");
        }

        return Results.Ok(new DoctorSettingsDto(doctor.CancellationPolicyHours));
    }
    catch (Exception ex)
    {
        Console.WriteLine("Error in /api/doctors/me/settings: " + ex.ToString());
        return Results.Problem("An internal server error occurred.");
    }
}).RequireAuthorization("Doctor");

app.MapPut("/api/doctors/me/settings", async (DoctorSettingsDto settings, HttpContext context, DoctorDbContext db) =>
{
    var userIdClaim = context.User.FindFirst(ClaimTypes.NameIdentifier);
    if (userIdClaim == null || !int.TryParse(userIdClaim.Value, out var userId))
    {
        return Results.Unauthorized();
    }

    var doctor = await db.Doctors.FirstOrDefaultAsync(d => d.UserId == userId);
    if (doctor == null)
    {
        return Results.NotFound("Doctor record not found for this user.");
    }

    if (settings.CancellationPolicyHours < 0)
    {
        return Results.BadRequest("Cancellation policy hours cannot be negative.");
    }

    doctor.CancellationPolicyHours = settings.CancellationPolicyHours;
    await db.SaveChangesAsync();

    return Results.Ok(new { message = "Settings updated successfully." });
}).RequireAuthorization("Doctor");

app.MapGet("/api/patients", async (DoctorDbContext db) =>
{
    return await db.Patients.ToListAsync();
}).RequireAuthorization("Doctor");

app.MapGet("/api/patients/user/{userId}", async (int userId, DoctorDbContext db) =>
{
    var patient = await db.Patients
        .Where(p => p.UserId == userId)
        .Select(p => new { p.Id, p.Name, p.UserId })
        .FirstOrDefaultAsync();

    if (patient == null)
    {
        return Results.NotFound("Patient not found for this user.");
    }
    return Results.Ok(patient);
}).RequireAuthorization();


app.MapGet("/api/medicalrecords", async (HttpContext context, DoctorDbContext db) =>
{
    var user = context.User;

    IQueryable<MedicalRecord> query = db.MedicalRecords;

    if (user.IsInRole("Doctor"))
    {
        var userIdClaim = user.FindFirst(ClaimTypes.NameIdentifier);
        if (userIdClaim != null && int.TryParse(userIdClaim.Value, out var userId))
        {
            var doctor = await db.Doctors.AsNoTracking().FirstOrDefaultAsync(d => d.UserId == userId);
            if (doctor != null)
            {
                query = query.Where(mr => mr.DoctorId == doctor.Id);
            }
            else
            {
                return Results.Ok(new List<object>());
            }
        }
    }

    var records = await query
        .Include(mr => mr.Patient)
        .Select(mr => new
        {
            mr.Id,
            mr.PatientId,
            Patient = mr.Patient == null ? null : new { mr.Patient.Id, mr.Patient.Name },
            mr.DoctorId,
            mr.RecordDate,
            mr.Diagnosis,
            mr.Treatment,
            mr.Notes
        })
        .ToListAsync();

    return Results.Ok(records);
}).RequireAuthorization("Doctor");


app.MapPost("/api/doctors", async (CreateDoctorDto doctorDto, DoctorDbContext db) =>
{
    var user = await db.Users.FindAsync(doctorDto.UserId);
    if (user == null)
    {
        return Results.NotFound("User not found.");
    }

    var existingDoctor = await db.Doctors.FirstOrDefaultAsync(d => d.UserId == doctorDto.UserId);
    if (existingDoctor != null)
    {
        return Results.Conflict("This user already has a doctor profile.");
    }

    var doctor = new Doctor
    {
        Name = doctorDto.Name,
        Specialty = doctorDto.Specialty,
        UserId = doctorDto.UserId
    };

    db.Doctors.Add(doctor);
    await db.SaveChangesAsync();
    return Results.Created($"/api/doctors/{doctor.Id}", doctor);
}).RequireAuthorization("Admin");

app.MapDelete("/api/doctors/{id}", async (int id, DoctorDbContext db) =>
{
    var doctor = await db.Doctors.FindAsync(id);
    if (doctor is null) return Results.NotFound();
    db.Doctors.Remove(doctor);
    await db.SaveChangesAsync();
    return Results.NoContent();
}).RequireAuthorization("Admin");

// --- Schedule Endpoints ---
app.MapGet("/api/doctors/{id}/availability", async (int id, string? date, DoctorDbContext db) =>
{
    try
    {
        var now = DateTime.UtcNow;
        var query = db.DoctorAvailabilities.AsNoTracking()
                      .Where(da => da.DoctorId == id && da.EndTime > now);

        if (DateOnly.TryParse(date, out var requestedDateOnly))
        {
            var requestedDate = requestedDateOnly.ToDateTime(TimeOnly.MinValue, DateTimeKind.Utc);
            var nextDay = requestedDate.AddDays(1);
            query = query.Where(da => da.StartTime < nextDay && da.EndTime > requestedDate);
        }
        else if (!string.IsNullOrEmpty(date))
        {
            return Results.Ok(new List<DoctorAvailability>());
        }
    
        return Results.Ok(await query.OrderBy(da => da.StartTime).ToListAsync());
    }
    catch (Exception ex)
    {
        Console.WriteLine("Error in /api/doctors/{id}/availability: " + ex.ToString());
        return Results.Problem("An internal server error occurred.");
    }
}).RequireAuthorization();
app.MapPost("/api/doctors/{id}/availability", async (int id, DoctorAvailability availability, DoctorDbContext db) =>
{
    if (id != availability.DoctorId)
    {
        return Results.BadRequest("Doctor ID mismatch");
    }
    // Basic validation: ensure EndTime is after StartTime
    if (availability.EndTime <= availability.StartTime)
    {
        return Results.BadRequest("End time must be after start time.");
    }

    db.DoctorAvailabilities.Add(availability);
    await db.SaveChangesAsync();
    return Results.Created($"/api/availability/{availability.Id}", availability);
});

app.MapDelete("/api/availability/{id}", async (int id, DoctorDbContext db) =>
{
    var availability = await db.DoctorAvailabilities.FindAsync(id);
    if (availability is null) return Results.NotFound();
    db.DoctorAvailabilities.Remove(availability);
    await db.SaveChangesAsync();
    return Results.NoContent();
}).RequireAuthorization("Admin", "Doctor");


app.MapGet("/api/medicalrecords/{patientId}", async (int patientId, DoctorDbContext db) =>
{
    return await db.MedicalRecords.Include(mr => mr.Doctor).Where(mr => mr.PatientId == patientId).ToListAsync();
}).RequireAuthorization("Doctor");

app.MapPost("/api/medicalrecords", async (MedicalRecord medicalRecord, DoctorDbContext db) =>
{
    db.MedicalRecords.Add(medicalRecord);
    await db.SaveChangesAsync();
    return Results.Created($"/api/medicalrecords/{medicalRecord.Id}", medicalRecord);
}).RequireAuthorization("Doctor");

app.MapPut("/api/medicalrecords/{id}", async (int id, MedicalRecord inputRecord, DoctorDbContext db) =>
{
    var record = await db.MedicalRecords.FindAsync(id);
    if (record is null) return Results.NotFound();
    record.Diagnosis = inputRecord.Diagnosis;
    record.Treatment = inputRecord.Treatment;
    record.Notes = inputRecord.Notes;
    await db.SaveChangesAsync();
    return Results.NoContent();
}).RequireAuthorization("Doctor");

app.MapDelete("/api/medicalrecords/{id}", async (int id, DoctorDbContext db) =>
{
    var record = await db.MedicalRecords.FindAsync(id);
    if (record is null) return Results.NotFound();
    db.MedicalRecords.Remove(record);
    await db.SaveChangesAsync();
    return Results.NoContent();
}).RequireAuthorization("Doctor");

app.MapGet("/api/appointments", async (DoctorDbContext db) =>
{
    return await db.Appointments.Include(a => a.Patient).Include(a => a.Doctor).ToListAsync();
});

app.MapGet("/api/appointments/my-appointments", async (HttpContext context, DoctorDbContext db) =>
{
    var userIdClaim = context.User.FindFirst(ClaimTypes.NameIdentifier);
    if (userIdClaim == null || !int.TryParse(userIdClaim.Value, out var userId))
    {
        return Results.Unauthorized();
    }

    var patient = await db.Patients.FirstOrDefaultAsync(p => p.UserId == userId);
    if (patient == null)
    {
        return Results.NotFound("Patient record not found for this user.");
    }

    var appointments = await db.Appointments
        .Where(a => a.PatientId == patient.Id)
        .Include(a => a.Doctor)
        .Select(a => new
        {
            a.Id,
            a.AppointmentTime,
            DoctorName = a.Doctor != null ? a.Doctor.Name : "未知醫師",
            DoctorSpecialty = a.Doctor != null ? a.Doctor.Specialty : "無",
            CancellationPolicyHours = a.Doctor != null ? a.Doctor.CancellationPolicyHours : 48 // Default to 48 if not set
        })
        .ToListAsync();

    return Results.Ok(appointments);
}).RequireAuthorization();

app.MapGet("/api/appointments/doctor", async (HttpContext context, DoctorDbContext db, string? date) =>
{
    var userIdClaim = context.User.FindFirst(ClaimTypes.NameIdentifier);
    if (userIdClaim == null || !int.TryParse(userIdClaim.Value, out var userId))
    {
        return Results.Unauthorized();
    }

    var doctor = await db.Doctors.AsNoTracking().FirstOrDefaultAsync(d => d.UserId == userId);
    if (doctor == null)
    {
        return Results.NotFound("Doctor record not found for this user.");
    }

    var query = db.Appointments.AsNoTracking()
        .Where(a => a.DoctorId == doctor.Id);

    if (DateOnly.TryParse(date, out var requestedDateOnly))
    {
        var requestedDate = requestedDateOnly.ToDateTime(TimeOnly.MinValue, DateTimeKind.Utc);
        var nextDay = requestedDate.AddDays(1);
        query = query.Where(a => a.AppointmentTime >= requestedDate && a.AppointmentTime < nextDay);
    }

    var appointments = await query
        .Include(a => a.Patient)
        .Select(a => new
        {
            a.Id,
            a.AppointmentTime,
            PatientName = a.Patient != null ? a.Patient.Name : "未知病患"
        })
        .OrderBy(a => a.AppointmentTime)
        .ToListAsync();

    return Results.Ok(appointments);
}).RequireAuthorization("Doctor");

app.MapPost("/api/appointments", async (AppointmentDto appointmentDto, HttpContext context, DoctorDbContext db) =>
{
    var userIdClaim = context.User.FindFirst(ClaimTypes.NameIdentifier);
    if (userIdClaim == null || !int.TryParse(userIdClaim.Value, out var userId))
    {
        return Results.Unauthorized();
    }

    int patientIdToUse;

    if (context.User.IsInRole("Doctor"))
    {
        if (appointmentDto.PatientId == null)
        {
            return Results.BadRequest("PatientId is required for doctors booking an appointment.");
        }
        var patientExists = await db.Patients.AnyAsync(p => p.Id == appointmentDto.PatientId.Value);
        if (!patientExists)
        {
            return Results.BadRequest("The specified patient does not exist.");
        }
        patientIdToUse = appointmentDto.PatientId.Value;
    }
    else // User is a regular user (patient)
    {
        var patient = await db.Patients.FirstOrDefaultAsync(p => p.UserId == userId);
        if (patient == null)
        {
            return Results.BadRequest("Patient record not found for the current user.");
        }
        patientIdToUse = patient.Id;
    }

    var appointment = new Appointment
    {
        PatientId = patientIdToUse,
        DoctorId = appointmentDto.DoctorId,
        AppointmentTime = appointmentDto.AppointmentTime,
        Notes = appointmentDto.Notes
    };

    // 1. Check if the doctor exists
    var doctorExists = await db.Doctors.AnyAsync(d => d.Id == appointment.DoctorId);
    if (!doctorExists)
    {
        return Results.BadRequest("Doctor not found.");
    }

    // 2. Check if the doctor is available at the requested time
    var doctorAvailability = await db.DoctorAvailabilities.FirstOrDefaultAsync(da =>
        da.DoctorId == appointment.DoctorId &&
        da.StartTime <= appointment.AppointmentTime &&
        da.EndTime > appointment.AppointmentTime);

    if (doctorAvailability == null)
    {
        return Results.BadRequest("Doctor is not available at the requested time.");
    }

    // 3. Check for existing appointments (double booking)
    var existingAppointment = await db.Appointments.AnyAsync(a =>
        a.DoctorId == appointment.DoctorId &&
        a.AppointmentTime == appointment.AppointmentTime);

    if (existingAppointment)
    {
        return Results.Conflict("This time slot is already booked.");
    }

    db.Appointments.Add(appointment);
    await db.SaveChangesAsync();
    return Results.Created($"/api/appointments/{appointment.Id}", appointment);
});

app.MapDelete("/api/appointments/{id}", async (int id, HttpContext context, DoctorDbContext db) =>
{
    var appointment = await db.Appointments.Include(a => a.Doctor).FirstOrDefaultAsync(a => a.Id == id);
    if (appointment is null) return Results.NotFound();

    var userIdClaim = context.User.FindFirst(ClaimTypes.NameIdentifier);
    if (userIdClaim == null || !int.TryParse(userIdClaim.Value, out var userId))
    {
        return Results.Unauthorized();
    }

    // Admin can bypass all checks
    if (!context.User.IsInRole("Admin"))
    {
        var patient = await db.Patients.AsNoTracking().FirstOrDefaultAsync(p => p.UserId == userId);
        if (appointment.PatientId != patient?.Id)
        {
            // If not admin and not the correct patient, forbid.
            return Results.Forbid();
        }

        // Enforce cancellation policy
        if (appointment.Doctor != null)
        {
            var cancellationHours = appointment.Doctor.CancellationPolicyHours;
            if (appointment.AppointmentTime.Subtract(DateTime.UtcNow).TotalHours < cancellationHours)
            {
                return Results.BadRequest($"Appointment cannot be cancelled within {cancellationHours} hours.");
            }
        }
    }

    db.Appointments.Remove(appointment);
    await db.SaveChangesAsync();
    return Results.NoContent();
}).RequireAuthorization(); // Requires any authenticated user

app.MapGet("/api/acupuncturepoints", async (DoctorDbContext db) =>
{
    return await db.AcupuncturePoints.ToListAsync();
});

// --- Public Availability Endpoints ---
app.MapGet("/api/availability/doctors", async (string date, DoctorDbContext db) =>
{
    try
    {
        if (!DateOnly.TryParse(date, out var requestedDateOnly))
        {
            return Results.BadRequest("Invalid date format. Please use YYYY-MM-DD.");
        }

        var requestedDate = requestedDateOnly.ToDateTime(TimeOnly.MinValue, DateTimeKind.Utc);
        var nextDay = requestedDate.AddDays(1);

        // 1. Get all doctors
        var doctors = await db.Doctors.AsNoTracking().ToListAsync();

        // 2. Get all relevant availabilities and appointments in fewer database calls
        var allAvailabilities = await db.DoctorAvailabilities.AsNoTracking()
            .Where(da => da.StartTime < nextDay && da.EndTime > requestedDate)
            .ToListAsync();

        var allAppointments = await db.Appointments.AsNoTracking()
            .Where(a => a.AppointmentTime >= requestedDate && a.AppointmentTime < nextDay)
            .ToListAsync();

        var availableDoctors = new List<AvailableDoctorDto>();

        // 3. Process each doctor
        foreach (var doctor in doctors)
        {
            var doctorAvailabilities = allAvailabilities.Where(da => da.DoctorId == doctor.Id).ToList();
            var doctorAppointments = allAppointments.Where(a => a.DoctorId == doctor.Id).Select(a => a.AppointmentTime).ToHashSet();

            var availableSlots = new SortedSet<TimeOnly>();

            foreach (var availability in doctorAvailabilities)
            {
                // Determine the time range for the current availability block that falls within the requested date
                var slotStart = availability.StartTime > requestedDate ? availability.StartTime : requestedDate;
                var slotEnd = availability.EndTime < nextDay ? availability.EndTime : nextDay;

                // Ensure slotStart and slotEnd are UTC
                slotStart = DateTime.SpecifyKind(slotStart, DateTimeKind.Utc);
                slotEnd = DateTime.SpecifyKind(slotEnd, DateTimeKind.Utc);

                // Round up the start time to the next 30-minute interval if it's not already on one
                if (slotStart.Minute % 30 != 0)
                {
                    slotStart = slotStart.AddMinutes(30 - (slotStart.Minute % 30));
                }

                // Generate slots
                while (slotStart < slotEnd)
                {
                    // Check if the slot is already booked
                    if (!doctorAppointments.Contains(slotStart))
                    {
                        availableSlots.Add(TimeOnly.FromDateTime(slotStart));
                    }
                    slotStart = slotStart.AddMinutes(30); // Move to the next potential slot
                }
            }

            if (availableSlots.Any())
            {
                availableDoctors.Add(new AvailableDoctorDto
                {
                    Id = doctor.Id,
                    Name = doctor.Name,
                    Specialty = doctor.Specialty,
                    AvailableSlots = availableSlots.ToList()
                });
            }
        }

        return Results.Ok(availableDoctors);
    }
    catch (Exception ex)
    {
        Console.WriteLine("Error in /api/availability/doctors: " + ex.ToString());
        return Results.Problem("An internal server error occurred.");
    }
});

app.Run();
