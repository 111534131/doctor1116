using System;

namespace Backend.Models
{
    public class DoctorAvailability
    {
        public int Id { get; set; }
        public int DoctorId { get; set; }
        public Doctor? Doctor { get; set; }
        
        // Change from DateOnly/TimeOnly to DateTime
        public DateTime StartTime { get; set; }
        public DateTime EndTime { get; set; }
    }
}