<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear the permissions table before seeding
        DB::table('permissions')->truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $now = now();
        DB::table('permissions')->insert([
            // Student Management
            ['slug' => 'student_view', 'name' => 'View Student Details', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'student_add', 'name' => 'Add New Student', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'student_edit', 'name' => 'Edit Student Details', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'student_delete', 'name' => 'Delete Student', 'created_at' => $now, 'updated_at' => $now],
            // Teacher Management
            ['slug' => 'teacher_view', 'name' => 'View Teacher Details', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'teacher_add', 'name' => 'Add New Teacher', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'teacher_edit', 'name' => 'Edit Teacher Details', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'teacher_delete', 'name' => 'Delete Teacher', 'created_at' => $now, 'updated_at' => $now],
            // Master Data
            ['slug' => 'academic_year_manage', 'name' => 'Manage Academic Years', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'class_manage', 'name' => 'Manage Classes', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'room_manage', 'name' => 'Manage Room Master', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'section_manage', 'name' => 'Manage Sections', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'subject_manage', 'name' => 'Manage Subjects', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'role_view', 'name' => 'View Roles', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'role_add', 'name' => 'Add Roles', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'role_edit', 'name' => 'Edit Roles', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'role_delete', 'name' => 'Delete Roles', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'parent_manage', 'name' => 'Manage Parents', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'certificate_manage', 'name' => 'Manage Certificates', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'certificate_view', 'name' => 'View Certificates', 'created_at' => $now, 'updated_at' => $now],
            // Timetable
            ['slug' => 'timetable_view', 'name' => 'View Timetable', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'timetable_create', 'name' => 'Create Timetable', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'timetable_edit', 'name' => 'Edit Timetable', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'timetable_delete', 'name' => 'Delete Timetable', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'timetable_teacher_view', 'name' => 'View Teacher Timetable', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'timetable_student_view', 'name' => 'View Student Timetable', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'timetable.manage_all', 'name' => 'Manage All Timetables', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'timetable.view_own', 'name' => 'View Own Timetable', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'timetable.view_class', 'name' => 'View Class Timetable', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'timetable.view_child', 'name' => 'View Child Timetable', 'created_at' => $now, 'updated_at' => $now],
            // Attendance
            ['slug' => 'attendance_mark', 'name' => 'Mark Daily Attendance', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'attendance_view', 'name' => 'View Attendance', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'attendance_report', 'name' => 'Attendance Reports', 'created_at' => $now, 'updated_at' => $now],
            // Homework
            ['slug' => 'homework_create', 'name' => 'Create Homework', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'homework_list', 'name' => 'View Homework List', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'homework_submission', 'name' => 'View Homework Submissions', 'created_at' => $now, 'updated_at' => $now],
            // Examination
            ['slug' => 'exam_type', 'name' => 'Manage Exam Types', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'exam_schedule', 'name' => 'Manage Exam Schedules', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'marks_entry', 'name' => 'Enter/Edit Marks', 'created_at' => $now, 'updated_at' => $now],
            // Others
            ['slug' => 'result_view', 'name' => 'View Results', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'notice_manage', 'name' => 'Manage Announcements', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'notice_view', 'name' => 'View Announcements', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'report_view', 'name' => 'View System Reports', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'setting_manage', 'name' => 'Manage Settings', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // Map Permissions to Roles
        $superAdmin = Role::where('id', 1)->first();
        $admin = Role::where('id', 2)->first(); // Admin
        $teacher = Role::where('id', 3)->first(); // Teacher
        $student = Role::where('id', 4)->first(); // Student
        $parent = Role::where('id', 5)->first(); // Parent

        if ($superAdmin) {
            $superAdmin->permissions()->sync(Permission::all());
        }

        if ($admin) {
            $admin->permissions()->sync(
                Permission::whereNotIn('slug', [
                    'timetable_delete',
                    'exam_type',
                    'marks_entry',
                ])->pluck('id')
            );
        }

        if ($teacher) {
            $teacher->permissions()->sync(
                Permission::whereIn('slug', [
                    'timetable_teacher_view',
                    'timetable.view_own',
                    'attendance_mark',
                    'attendance_view',
                    'homework_create',
                    'homework_list',
                    'homework_submission',
                    'exam_type',
                    'exam_schedule',
                    'marks_entry',
                    'notice_view',
                    'result_view',
                ])->pluck('id')
            );
        }

        if ($student) {
            $student->permissions()->sync(
                Permission::whereIn('slug', [
                    'timetable_student_view',
                    'timetable.view_class',
                    'attendance_view',
                    'homework_list',
                    'notice_view',
                    'exam_schedule',
                    'result_view',
                ])->pluck('id')
            );
        }

        if ($parent) {
            $parent->permissions()->sync(
                Permission::whereIn('slug', [
                    'timetable_student_view',
                    'timetable.view_child',
                    'attendance_view',
                    'homework_list',
                    'notice_view',
                    'exam_schedule',
                    'result_view',
                ])->pluck('id')
            );
        }
    }
}
