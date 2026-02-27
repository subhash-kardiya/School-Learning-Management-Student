<?php $__env->startSection('title', 'Faculty Profile View'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid py-4 teacher-module-compact">

        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <a href="<?php echo e(route('teachers.index')); ?>" class="btn btn-link text-decoration-none text-muted mb-2">
                    <i class="fas fa-arrow-left me-1"></i> Back to Faculty Directory
                </a>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="<?php echo e(route('teachers.edit', $teacher->id)); ?>" class="btn btn-gradient-primary shadow-sm">
                    <i class="fa fa-pen me-2"></i> Edit Profile
                </a>
            </div>
        </div>

        <div class="row g-4">
            <!-- Profile Column -->
            <div class="col-lg-4">
                <div class="card profile-card h-100 d-flex flex-column align-items-center p-4 text-center">
                    <?php
                        $avatar =
                            'https://ui-avatars.com/api/?name=' .
                            urlencode($teacher->name) .
                            '&background=5D59E0&color=fff&size=200';
                        if ($teacher->profile_image) {
                            $avatar = asset('uploads/teachers/' . $teacher->profile_image);
                        }
                    ?>
                    <div class="avatar-wrapper mb-3">
                        <img src="<?php echo e($avatar); ?>" class="profile-avatar shadow-lg">
                    </div>

                    <h4 class="fw-bold text-dark mb-1"><?php echo e($teacher->name); ?></h4>
                    <p class="text-muted small mb-3">System Identity: <?php echo e($teacher->username); ?></p>

                    <div class="d-flex justify-content-center gap-2 mb-4 flex-wrap">
                        <span class="badge badge-gradient-info hover-scale">Faculty</span>
                        <?php if($teacher->status == 1): ?>
                            <span class="badge badge-gradient-success hover-scale">Active</span>
                        <?php else: ?>
                            <span class="badge badge-gradient-danger hover-scale">Inactive</span>
                        <?php endif; ?>
                    </div>

                    <div class="border-top pt-4 text-start w-100">
                        <h6 class="fw-bold text-dark small text-uppercase mb-3">Contact Channels</h6>
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-light rounded p-2 me-3"><i class="fas fa-envelope text-primary"></i></div>
                            <div>
                                <div class="text-muted small">Official Email</div>
                                <div class="small fw-bold text-dark text-break"><?php echo e($teacher->email); ?></div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="bg-light rounded p-2 me-3"><i class="fas fa-phone text-primary"></i></div>
                            <div>
                                <div class="text-muted small">Mobile No</div>
                                <div class="small fw-bold text-dark"><?php echo e($teacher->mobile_no ?? 'N/A'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Details Column -->
            <div class="col-lg-8">
                <div class="card dossier-card h-100 p-0 border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom-0 d-flex align-items-center gap-2">
                        <i class="fas fa-id-badge text-primary fs-4"></i>
                        <h5 class="card-title fw-bold mb-0">Professional Dossier</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">

                            <!-- Employment Section -->
                            <div class="col-12 section-card p-3">
                                <div class="d-flex align-items-center mb-3 gap-2">
                                    <i class="fas fa-briefcase text-primary fs-5"></i>
                                    <h6 class="fw-bold text-primary mb-0">Employment & Qualifications</h6>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label text-muted">Join Date</label>
                                        <div class="fw-bold text-dark">
                                            <?php echo e($teacher->join_date ? \Carbon\Carbon::parse($teacher->join_date)->format('M d, Y') : 'N/A'); ?>

                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted">Years of Experience</label>
                                        <div class="fw-bold text-dark">
                                            <?php echo e($teacher->exp ? $teacher->exp . ' Years' : 'N/A'); ?></div>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label text-muted">Primary Qualification</label>
                                        <div class="fw-bold text-dark"><?php echo e($teacher->qualification ?? 'N/A'); ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Personal Section -->
                            <div class="col-12 section-card p-3 mt-3">
                                <div class="d-flex align-items-center mb-3 gap-2">
                                    <i class="fas fa-user text-primary fs-5"></i>
                                    <h6 class="fw-bold text-primary mb-0">Personal Records</h6>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label text-muted">Gender Identity</label>
                                        <div class="fw-bold text-dark text-capitalize"><?php echo e($teacher->gender ?? 'N/A'); ?>

                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted">Date of Birth</label>
                                        <div class="fw-bold text-dark">
                                            <?php echo e($teacher->date_of_birth ? \Carbon\Carbon::parse($teacher->date_of_birth)->format('M d, Y') : 'N/A'); ?>

                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label text-muted">Residential Location</label>
                                        <div class="fw-bold text-dark">
                                            <?php echo e($teacher->address); ?><br>
                                            <?php echo e($teacher->city); ?>, <?php echo e($teacher->state); ?> <?php echo e($teacher->pincode); ?>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Subjects Section -->
                            <div class="col-12 section-card p-3 mt-3">
                                <div class="d-flex align-items-center mb-3 gap-2">
                                    <i class="fas fa-book text-primary fs-5"></i>
                                    <h6 class="fw-bold text-primary mb-0">Assigned Subjects</h6>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php if($teacher->subjects && $teacher->subjects->count()): ?>
                                        <?php $__currentLoopData = $teacher->subjects->unique('id'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <span class="badge bg-light text-dark border">
                                                <?php echo e($subject->name); ?> (<?php echo e($subject->subject_code); ?>)
                                            </span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php else: ?>
                                        <span class="text-muted small">No subjects assigned</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

  
<?php $__env->stopSection(); ?>

<?php $__env->startPush('css'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/resize/teacher-compact.css')); ?>">
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\pc\OneDrive\Desktop\school_lms\resources\views/admin/teachers/show.blade.php ENDPATH**/ ?>
