using System.Collections.Generic;

namespace Backend.Models
{
    public class Doctor
    {
        public int Id { get; set; }
        public string? Name { get; set; }
        public string? Specialty { get; set; }
        public string? ContactInfo { get; set; }
        public int CancellationPolicyHours { get; set; } = 48;

        // Foreign key for User
        public int UserId { get; set; }
        public User User { get; set; } = null!;

        public ICollection<Appointment> Appointments { get; set; } = new List<Appointment>();
        public ICollection<MedicalRecord> MedicalRecords { get; set; } = new List<MedicalRecord>();
        public ICollection<DoctorAvailability> Availabilities { get; set; } = new List<DoctorAvailability>();
    }
}