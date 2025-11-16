using System;
using Microsoft.EntityFrameworkCore.Migrations;

#nullable disable

namespace Backend.Migrations
{
    /// <inheritdoc />
    public partial class AddDoctorAvailabilityRelationshipFinal : Migration
    {
        /// <inheritdoc />
        protected override void Up(MigrationBuilder migrationBuilder)
        {
            // migrationBuilder.DropColumn(
            //     name: "Date",
            //     table: "DoctorAvailabilities");

            migrationBuilder.AddColumn<int>(
                name: "UserId",
                table: "Doctors",
                type: "int",
                nullable: false,
                defaultValue: 0);

            migrationBuilder.AlterColumn<DateTime>(
                name: "StartTime",
                table: "DoctorAvailabilities",
                type: "datetime(6)",
                nullable: false,
                oldClrType: typeof(TimeOnly),
                oldType: "time(6)");

            migrationBuilder.AlterColumn<DateTime>(
                name: "EndTime",
                table: "DoctorAvailabilities",
                type: "datetime(6)",
                nullable: false,
                oldClrType: typeof(TimeOnly),
                oldType: "time(6)");

            migrationBuilder.CreateIndex(
                name: "IX_Doctors_UserId",
                table: "Doctors",
                column: "UserId");

            migrationBuilder.AddForeignKey(
                name: "FK_Doctors_Users_UserId",
                table: "Doctors",
                column: "UserId",
                principalTable: "Users",
                principalColumn: "Id",
                onDelete: ReferentialAction.Cascade);
        }

        /// <inheritdoc />
        protected override void Down(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.DropForeignKey(
                name: "FK_Doctors_Users_UserId",
                table: "Doctors");

            migrationBuilder.DropIndex(
                name: "IX_Doctors_UserId",
                table: "Doctors");

            // migrationBuilder.DropColumn(
            //     name: "UserId",
            //     table: "Doctors");

            // migrationBuilder.AlterColumn<TimeOnly>(
            //     name: "StartTime",
            //     table: "DoctorAvailabilities",
            //     type: "time(6)",
            //     nullable: false,
            //     oldClrType: typeof(DateTime),
            //     oldType: "datetime(6)");

            // migrationBuilder.AlterColumn<TimeOnly>(
            //     name: "EndTime",
            //     table: "DoctorAvailabilities",
            //     type: "time(6)",
            //     nullable: false,
            //     oldClrType: typeof(DateTime),
            //     oldType: "datetime(6)");

            // migrationBuilder.AddColumn<DateOnly>(
            //     name: "Date",
            //     table: "DoctorAvailabilities",
            //     type: "date",
            //     nullable: false,
            //     defaultValue: new DateOnly(1, 1, 1));
        }
    }
}
