<?php $__env->startSection('title', 'Student Full Profile'); ?>
<?php $__env->startPush('css'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/student-show.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $img = $student->profile_image
            ? asset('uploads/students/' . $student->profile_image)
            : 'https://ui-avatars.com/api/?name=' .
                urlencode($student->student_name) .
                '&background=6366f1&color=fff&size=200';
    ?>
    <div class="container-fluid py-4 student-view-modern student-module-compact">

        <!-- TOP BAR -->
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <a href="<?php echo e(route('students.index')); ?>" class="text-decoration-none top-link">
                <i class="fas fa-arrow-left me-1"></i> Back to Student Directory
            </a>
            <a href="<?php echo e(route('students.edit', $student->id)); ?>" class="btn btn-primary-fancy">
                <i class="fa fa-pen me-1"></i> Edit Profile
            </a>
        </div>

        <div class="hero mb-4">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <img src="<?php echo e($img); ?>" class="profile-img" alt="Student">
                <div>
                    <h4 class="fw-bold mb-1"><?php echo e($student->student_name); ?></h4>
                    <div class="small mb-2"><?php echo e($student->email); ?></div>
                    <span class="badge bg-light text-dark me-1">Student</span>
                    <span class="badge <?php echo e($student->status ? 'bg-success' : 'bg-danger'); ?>">
                        <?php echo e($student->status ? 'Active' : 'Inactive'); ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="row g-4">

            <!-- RIGHT DETAILS -->
            <div class="col-12">

                <!-- ACADEMIC -->
                <div class="panel mb-3">
                    <h5><i class="fa fa-graduation-cap"></i> Academic Information</h5>

                    <div class="detail-grid">
                        <div>
                            <small>Class</small>
                            <strong><?php echo e($student->class->name ?? 'N/A'); ?></strong>
                        </div>
                        <div>
                            <small>Roll Number</small>
                            <strong><?php echo e($student->roll_no ?? 'N/A'); ?></strong>
                        </div>
                        <div>
                            <small>Section</small>
                            <strong><?php echo e($student->section->name ?? 'N/A'); ?></strong>
                        </div>
                        <div>
                            <small>Academic Year</small>
                            <strong><?php echo e($student->academicYear->name ?? 'N/A'); ?></strong>
                        </div>
                        <div>
                            <small>Student Email</small>
                            <strong><?php echo e($student->email ?? 'N/A'); ?></strong>
                        </div>
                        <div>
                            <small>Student Mobile</small>
                            <strong><?php echo e($student->mobile_no ?? 'N/A'); ?></strong>
                        </div>
                    </div>
                </div>

                <!-- PERSONAL -->
                <div class="panel mb-3">
                    <h5><i class="fa fa-user"></i> Personal Records</h5>

                    <div class="detail-grid">
                        <div>
                            <small>Gender</small>
                            <strong><?php echo e(ucfirst($student->gender)); ?></strong>
                        </div>
                        <div>
                            <small>Date of Birth</small>
                            <strong><?php echo e($student->date_of_birth ?? 'N/A'); ?></strong>
                        </div>
                        <div>
                            <small>City</small>
                            <strong><?php echo e($student->city ?? 'N/A'); ?></strong>
                        </div>
                        <div>
                            <small>State</small>
                            <strong><?php echo e($student->state ?? 'N/A'); ?></strong>
                        </div>
                        <div>
                            <small>Pincode</small>
                            <strong><?php echo e($student->pincode ?? 'N/A'); ?></strong>
                        </div>
                        <div class="full">
                            <small>Address</small>
                            <strong><?php echo e($student->address ?? 'N/A'); ?></strong>
                        </div>
                    </div>
                </div>

                <!-- PARENT -->
                <div class="panel">
                    <h5><i class="fa fa-users"></i> Parent / Guardian</h5>

                    <div class="detail-grid">
                        <div>
                            <small>Name</small>
                            <strong><?php echo e($student->parent->parent_name ?? 'Not Linked'); ?></strong>
                        </div>
                        <div>
                            <small>Contact</small>
                            <strong><?php echo e($student->parent->mobile_no ?? 'N/A'); ?></strong>
                        </div>
                        <div>
                            <small>Username</small>
                            <strong><?php echo e($student->parent->username ?? 'N/A'); ?></strong>
                        </div>
                        <div>
                            <small>Email</small>
                            <strong><?php echo e($student->parent->email ?? 'N/A'); ?></strong>
                        </div>
                        <div>
                            <small>Status</small>
                            <strong>
                                <?php echo e(isset($student->parent)
                                    ? ((int) $student->parent->status === 1 ? 'Active' : 'Inactive')
                                    : 'Not Linked'); ?>
                            </strong>
                        </div>
                        <div class="full">
                            <small>Address</small>
                            <strong><?php echo e($student->parent->address ?? 'N/A'); ?></strong>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('css'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/resize/student-compact.css')); ?>">
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\pc\OneDrive\Desktop\school_lms\resources\views/admin/students/show.blade.php ENDPATH**/ ?>
