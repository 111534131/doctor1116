using System;
using System.Collections.Generic;

namespace Backend.Models
{
    public class Patient
    {
        public int Id { get; set; }
        public string? Name { get; set; }
        public string? ContactInfo { get; set; }
        public DateTime DateOfBirth { get; set; }
        public ICollection<Appointment> Appointments { get; set; } = new List<Appointment>();
        public ICollection<MedicalRecord> MedicalRecords { get; set; } = new List<MedicalRecord>();

        // Link to a User account
        public int UserId { get; set; }
        public User User { get; set; } = null!;
    }
}