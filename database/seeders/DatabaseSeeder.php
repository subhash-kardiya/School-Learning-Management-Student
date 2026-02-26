<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Call all seeders in proper order
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            AcademicYearSeeder::class,
            AdminSeeder::class,
            TeacherSeeder::class,
            ParentSeeder::class,
            ClassSeeder::class,
            SectionSeeder::class,
            SubjectSeeder::class,
            StudentSeeder::class,
            TeacherMappingSeeder::class,
            TimetableSeeder::class,
            HomeworkSeeder::class,
            HomeworkSubmissionSeeder::class,
            AttendanceSeeder::class,
            CertificateSeeder::class,
        ]);
    }
}
