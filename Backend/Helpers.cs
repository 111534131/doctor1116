using Backend.Data;
using Backend.Models;
using Microsoft.EntityFrameworkCore;
using System.IdentityModel.Tokens.Jwt;
using System.Security.Claims;
using System.Text;
using Microsoft.IdentityModel.Tokens;

namespace Backend
{
    public static class Helpers
    {
        public static string GenerateJwtToken(User user, string key, string issuer, string audience)
        {
            var securityKey = new SymmetricSecurityKey(Encoding.UTF8.GetBytes(key));
            var credentials = new SigningCredentials(securityKey, SecurityAlgorithms.HmacSha256);

            var claims = new[]
            {
                new Claim(JwtRegisteredClaimNames.Sub, user.Id.ToString()),
                new Claim(JwtRegisteredClaimNames.Email, user.Email ?? string.Empty),
                new Claim(JwtRegisteredClaimNames.Name, user.Username),
                new Claim(JwtRegisteredClaimNames.Jti, Guid.NewGuid().ToString()),
                new Claim(ClaimTypes.Role, user.Role)
            };

            var token = new JwtSecurityToken(
                issuer: issuer,
                audience: audience,
                claims: claims,
                expires: DateTime.Now.AddMinutes(120),
                signingCredentials: credentials);

            return new JwtSecurityTokenHandler().WriteToken(token);
        }

        public static async Task SeedData(IHost app)
        {
            using var scope = app.Services.CreateScope();
            var dbContext = scope.ServiceProvider.GetRequiredService<DoctorDbContext>();
            
            // Apply migrations
            await dbContext.Database.MigrateAsync();

            // Seed Users (only if table is empty)
            if (!await dbContext.Users.AnyAsync())
            {
                var users = new List<User>
                {
                    new User { Username = "Admin User", Email = "admin@example.com", PasswordHash = BCrypt.Net.BCrypt.HashPassword("admin123"), Role = "Admin" },
                    new User { Username = "Doctor User", Email = "doctor@example.com", PasswordHash = BCrypt.Net.BCrypt.HashPassword("doctor123"), Role = "Doctor" },
                    new User { Username = "Test User", Email = "user@example.com", PasswordHash = BCrypt.Net.BCrypt.HashPassword("user123"), Role = "User" }
                };
                await dbContext.Users.AddRangeAsync(users);
                await dbContext.SaveChangesAsync();
            }

            // Ensure Doctor entity exists for the doctor user (runs every time to be safe)
            var doctorUser = await dbContext.Users.FirstOrDefaultAsync(u => u.Email == "doctor@example.com");
            if (doctorUser != null)
            {
                if (!await dbContext.Doctors.AnyAsync(d => d.UserId == doctorUser.Id))
                {
                    var doctor = new Doctor
                    {
                        Name = doctorUser.Username,
                        Specialty = "General Physiotherapy",
                        ContactInfo = doctorUser.Email,
                        UserId = doctorUser.Id
                    };
                    dbContext.Doctors.Add(doctor);
                    await dbContext.SaveChangesAsync();
                }
            }
        }
    }
}
