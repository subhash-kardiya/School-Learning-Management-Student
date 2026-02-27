<?php $__env->startSection('title', 'Student Profile'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid py-4">

        <!-- TOP BAR -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="<?php echo e(route('students.index')); ?>" class="text-muted text-decoration-none">
                ← Back to Student Directory
            </a>
            <a href="<?php echo e(route('students.edit', $student->id)); ?>" class="btn btn-primary rounded-pill px-4">
                <i class="fa fa-pen me-1"></i> Edit Profile
            </a>
        </div>

        <div class="row g-4">

            <!-- LEFT PROFILE CARD -->
            <div class="col-lg-4 col-xl-3">
                <div class="profile-card">

                    <?php
                        $img = $student->profile_image
                            ? asset('uploads/students/' . $student->profile_image)
                            : 'https://ui-avatars.com/api/?name=' .
                                urlencode($student->student_name) .
                                '&background=6366f1&color=fff&size=200';
                    ?>

                    <img src="<?php echo e($img); ?>" class="profile-img">

                    <h4 class="fw-bold mt-3"><?php echo e($student->student_name); ?></h4>
                    <div class="text-muted small mb-2">
                        System Identity: <?php echo e($student->username); ?>

                    </div>

                    <div class="d-flex justify-content-center gap-2 mb-4">
                        <span class="badge role">Student</span>
                        <span class="badge <?php echo e($student->status ? 'active' : 'inactive'); ?>">
                            <?php echo e($student->status ? 'Active' : 'Inactive'); ?>

                        </span>
                    </div>

                    <hr>

                    <h6 class="section-title">Contact Channels</h6>

                    <div class="contact-item">
                        <i class="fa fa-envelope"></i>
                        <div>
                            <small>Email</small>
                            <div><?php echo e($student->email); ?></div>
                        </div>
                    </div>

                    <div class="contact-item">
                        <i class="fa fa-phone"></i>
                        <div>
                            <small>Mobile No</small>
                            <div><?php echo e($student->mobile_no ?? 'N/A'); ?></div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- RIGHT DETAILS -->
            <div class="col-lg-8 col-xl-9">

                <!-- ACADEMIC -->
                <div class="detail-card">
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
                    </div>
                </div>

                <!-- PERSONAL -->
                <div class="detail-card">
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
                <div class="detail-card">
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
                    </div>
                </div>

            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\pc\OneDrive\Desktop\school_lms\resources\views/admin/students/show.blade.php ENDPATH**/ ?>