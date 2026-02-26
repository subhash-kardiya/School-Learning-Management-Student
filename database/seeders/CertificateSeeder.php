<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Certificate;
use App\Models\Student;
use App\Models\AcademicYear;

class CertificateSeeder extends Seeder
{
    public function run(): void
    {
        $student1 = Student::orderBy('id')->first();
        $student2 = Student::orderBy('id')->skip(1)->first();
        if (!$student1) {
            return;
        }

        $year = AcademicYear::first();
        $issueDate = now()->toDateString();
        $certNo = 'CERT-' . now()->format('Y') . '-0001';

        Certificate::updateOrCreate(
            ['certificate_no' => $certNo],
            [
                'student_id' => $student1->id,
                'certificate_type' => 'bonafide',
                'academic_year_id' => $year?->id,
                'issue_date' => $issueDate,
                'status' => 'issued',
            ]
        );

        if ($student2) {
            $certNo2 = 'CERT-' . now()->format('Y') . '-0002';
            Certificate::updateOrCreate(
                ['certificate_no' => $certNo2],
                [
                    'student_id' => $student2->id,
                    'certificate_type' => 'bonafide',
                    'academic_year_id' => $year?->id,
                    'issue_date' => $issueDate,
                    'status' => 'issued',
                ]
            );
        }
    }
}
