namespace Backend.Models
{
    public class AcupuncturePoint
    {
        public int Id { get; set; }
        public string? Name { get; set; }
        public string? BodyPart { get; set; }
        public string? Function { get; set; }
        public string? Harm { get; set; }
        public float CoordX { get; set; }
        public float CoordY { get; set; }
    }
}
