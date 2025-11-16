using System;

namespace Backend.Models
{
    public class Appointment
    {
        public int Id { get; set; }
        public int PatientId { get; set; }
        public Patient? Patient { get; set; } // Navigation property
        public int DoctorId { get; set; }
        public Doctor? Doctor { get; set; } // Navigation property
        public DateTime AppointmentTime { get; set; }
        public string? Status { get; set; } // e.g., "Scheduled", "Completed", "Cancelled"
        public string? Notes { get; set; }
    }
}