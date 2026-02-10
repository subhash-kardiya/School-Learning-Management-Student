<?php $__env->startSection('title', 'Edit Teacher Profile'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid px-2 py-2" style="min-height:100vh;">

        <!-- Header -->
        <div class="mb-4">
            <a href="<?php echo e(route('teachers.index')); ?>" class="btn btn-link text-decoration-none text-muted mb-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Faculty Directory
            </a>
        </div>

        <!-- Full-Width Form -->
        <div class="row">
            <div class="col-12">
                <form action="<?php echo e(route('teachers.update', $teacher->id)); ?>" method="POST" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>

                    <!-- Professional Identity -->
                    <div class="form-section p-4 mb-4 shadow-sm rounded-4 bg-white">
                        <h5 class="text-primary fw-bold mb-3">Professional Identity</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" value="<?php echo e($teacher->name); ?>"
                                    required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" value="<?php echo e($teacher->username); ?>"
                                    required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo e($teacher->email); ?>"
                                    required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Update Password</label>
                                <input type="password" name="password" class="form-control"
                                    placeholder="Leave blank to keep current">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Mobile No</label>
                                <input type="text" name="mobile_no" class="form-control"
                                    value="<?php echo e($teacher->mobile_no); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Gender</label>
                                <select name="gender" class="form-select">
                                    <option value="">Select</option>
                                    <option value="male" <?php echo e($teacher->gender == 'male' ? 'selected' : ''); ?>>Male</option>
                                    <option value="female" <?php echo e($teacher->gender == 'female' ? 'selected' : ''); ?>>Female
                                    </option>
                                    <option value="other" <?php echo e($teacher->gender == 'other' ? 'selected' : ''); ?>>Other
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Employment & Address -->
                    <div class="form-section p-4 mb-4 shadow-sm rounded-4 bg-white">
                        <h5 class="text-primary fw-bold mb-3">Employment & Address</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="date_of_birth" class="form-control"
                                    value="<?php echo e($teacher->date_of_birth); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Join Date</label>
                                <input type="date" name="join_date" class="form-control"
                                    value="<?php echo e($teacher->join_date); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Role</label>
                                <select name="role_id" class="form-select">
                                    <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($role->id); ?>"
                                            <?php echo e($teacher->role_id == $role->id ? 'selected' : ''); ?>>
                                            <?php echo e($role->name); ?>
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="2"><?php echo e($teacher->address); ?></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" value="<?php echo e($teacher->city); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">State</label>
                                <input type="text" name="state" class="form-control" value="<?php echo e($teacher->state); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Pincode</label>
                                <input type="text" name="pincode" class="form-control" value="<?php echo e($teacher->pincode); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Qualification</label>
                                <input type="text" name="qualification" class="form-control"
                                    value="<?php echo e($teacher->qualification); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Experience (Years)</label>
                                <input type="number" name="exp" class="form-control" value="<?php echo e($teacher->exp); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="1" <?php echo e($teacher->status == 1 ? 'selected' : ''); ?>>Active</option>
                                    <option value="0" <?php echo e($teacher->status == 0 ? 'selected' : ''); ?>>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Profile Avatar</label>
                                <input type="file" name="profile_image" class="form-control mt-2">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Assign Subjects</label>
                                <select name="subject_ids[]" class="form-select" multiple>
                                    <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($subject->id); ?>"
                                            <?php echo e(in_array($subject->id, $assignedSubjectIds ?? []) ? 'selected' : ''); ?>>
                                            <?php echo e($subject->name); ?> (<?php echo e($subject->subject_code); ?>)
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <small class="text-muted">Hold Ctrl (Windows) / Cmd (Mac) to select multiple.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="text-end">
                        <a href="<?php echo e(route('teachers.index')); ?>" class="btn btn-light me-2 btn-rounded">Discard</a>
                        <button type="submit" class="btn btn-gradient-primary btn-rounded px-5">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>

        
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\pc\OneDrive\Desktop\school_lms\resources\views/admin/teachers/edit.blade.php ENDPATH**/ ?>
