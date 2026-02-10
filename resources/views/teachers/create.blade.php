<?php $__env->startSection('title', 'Faculty Registration'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid px-2 py-2" style="min-height:100vh;">

        <!-- Header -->
        <div class="mb-4">
            <a href="<?php echo e(route('teachers.index')); ?>" class="btn btn-link text-decoration-none text-muted mb-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Faculty Directory
            </a>
        </div>

        <!-- Registration Form -->
        <div class="row">
            <div class="col-12">
                <div class="card p-4 shadow-sm rounded-4 bg-white form-card">
                    <form action="<?php echo e(route('teachers.store')); ?>" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>

                        <!-- Professional Identity -->
                        <h5 class="text-primary fw-bold mb-3">Professional Identity</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Full Name</label>
                                <input name="name" class="form-control form-control-modern"
                                    placeholder="e.g. Dr. Sarah Smith" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">System Username</label>
                                <input name="username" class="form-control form-control-modern"
                                    placeholder="Unique username" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Email Address</label>
                                <input name="email" type="email" class="form-control form-control-modern"
                                    placeholder="e.g. sarah@school.edu" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Security Password</label>
                                <input type="password" name="password" class="form-control form-control-modern"
                                    placeholder="Min. 8 characters" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Contact Number</label>
                                <input name="mobile_no" class="form-control form-control-modern"
                                    placeholder="e.g. +1 555 0123">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Gender Identity</label>
                                <select name="gender" class="form-select form-select-modern">
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <!-- Personal & Employment Details -->
                        <h5 class="text-primary fw-bold mt-5 mb-3">Personal & Employment Details</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="date_of_birth" class="form-control form-control-modern">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Joining Date</label>
                                <input type="date" name="join_date" class="form-control form-control-modern">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Security Role</label>
                                <select name="role_id" class="form-select form-select-modern">
                                    <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($role->id); ?>" <?php echo e((isset($teacherRole) && $teacherRole && $teacherRole->id == $role->id) ? 'selected' : ''); ?>>
                                            <?php echo e($role->name); ?>
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Assign Subjects</label>
                                <select name="subject_ids[]" class="form-select form-select-modern" multiple>
                                    <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($subject->id); ?>">
                                            <?php echo e($subject->name); ?> (<?php echo e($subject->subject_code); ?>)
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <small class="text-muted">Hold Ctrl (Windows) / Cmd (Mac) to select multiple.</small>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Residential Address</label>
                                <textarea name="address" class="form-control form-control-modern" rows="2" placeholder="Street, Apartment, Unit"></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">City</label>
                                <input name="city" class="form-control form-control-modern" placeholder="e.g. New York">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">State / Province</label>
                                <input name="state" class="form-control form-control-modern" placeholder="e.g. NY">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Pincode</label>
                                <input name="pincode" class="form-control form-control-modern" placeholder="e.g. 10001">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Highest Qualification</label>
                                <input name="qualification" class="form-control form-control-modern"
                                    placeholder="e.g. PhD in Education">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Teaching Experience (Years)</label>
                                <input name="exp" type="number" class="form-control form-control-modern"
                                    placeholder="e.g. 5">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Employment Status</label>
                                <select name="status" class="form-select form-select-modern">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Profile Avatar</label>
                                <input type="file" name="profile_image" class="form-control form-control-modern">
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="text-end mt-5">
                            <a href="<?php echo e(route('teachers.index')); ?>" class="btn btn-light me-2 btn-rounded">Cancel
                                Registration</a>
                            <button type="submit" class="btn btn-gradient-primary btn-rounded px-5">Confirm Faculty
                                Registration</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <!-- Modern Styling -->
       
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\pc\OneDrive\Desktop\school_lms\resources\views/admin/teachers/create.blade.php ENDPATH**/ ?>
