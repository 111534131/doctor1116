using Microsoft.EntityFrameworkCore;
using Backend.Models;

namespace Backend.Data
{
    public class DoctorDbContext : DbContext
    {
        public DoctorDbContext(DbContextOptions<DoctorDbContext> options) : base(options)
        {
        }

        public DbSet<User> Users { get; set; }
        public DbSet<Doctor> Doctors { get; set; }
        public DbSet<Patient> Patients { get; set; }
        public DbSet<Appointment> Appointments { get; set; }
        public DbSet<MedicalRecord> MedicalRecords { get; set; }
        public DbSet<AcupuncturePoint> AcupuncturePoints { get; set; }
        public DbSet<DoctorAvailability> DoctorAvailabilities { get; set; }

        protected override void OnModelCreating(ModelBuilder modelBuilder)
        {
            base.OnModelCreating(modelBuilder);

            // Doctor -> Appointments
            modelBuilder.Entity<Doctor>()
                .HasMany(d => d.Appointments)
                .WithOne(a => a.Doctor)
                .HasForeignKey(a => a.DoctorId);

            // Doctor -> MedicalRecords
            modelBuilder.Entity<Doctor>()
                .HasMany(d => d.MedicalRecords)
                .WithOne(mr => mr.Doctor)
                .HasForeignKey(mr => mr.DoctorId);

            // Patient -> MedicalRecords
            modelBuilder.Entity<Patient>()
                .HasMany(p => p.MedicalRecords)
                .WithOne(mr => mr.Patient)
                .HasForeignKey(mr => mr.PatientId);

            // Doctor -> Availabilities
            modelBuilder.Entity<Doctor>()
                .HasMany(d => d.Availabilities)
                .WithOne(a => a.Doctor)
                .HasForeignKey(a => a.DoctorId);
        }
    }
}