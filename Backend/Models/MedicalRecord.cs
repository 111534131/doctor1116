using System;

namespace Backend.Models
{
    public class MedicalRecord
    {
        public int Id { get; set; }
        public int PatientId { get; set; }
        public Patient? Patient { get; set; } // Navigation property
        public int DoctorId { get; set; }
        public Doctor? Doctor { get; set; } // Navigation property
        public DateTime RecordDate { get; set; }
        public string? Diagnosis { get; set; }
        public string? Treatment { get; set; }
        public string? Notes { get; set; }
    }
}