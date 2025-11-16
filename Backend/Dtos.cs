namespace Backend
{
    public record UserRegistrationDto(string Username, string Email, string Password);
    public record UserLoginDto(string Email, string Password);
    public record GoogleLoginRequest(string Credential);
    public record UpdateRoleRequest(string Role);
    public record AvailableDoctorDto
    {
        public int Id { get; set; }
        public string? Name { get; set; }
        public string? Specialty { get; set; }
        public List<TimeOnly> AvailableSlots { get; set; } = new List<TimeOnly>();
    }
    public record AppointmentDto(int DoctorId, int? PatientId, DateTime AppointmentTime, string? Notes);
    public record DoctorSettingsDto(int CancellationPolicyHours);
    public record CreateDoctorDto(string Name, string Specialty, int UserId);
}
