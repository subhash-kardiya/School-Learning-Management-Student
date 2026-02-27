<?php $__env->startSection('title', 'Edit Parent'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid py-4">
        <div class="mb-4">
            <a href="<?php echo e(route('parents.index')); ?>" class="text-muted text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i> Back to Parents
            </a>
        </div>

        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4">
                <form action="<?php echo e(route('parents.update', $parent->id)); ?>" method="POST" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>

                    <h6 class="fw-bold text-primary border-bottom pb-2 mb-4">Parent Details</h6>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Parent Name</label>
                            <input type="text" name="parent_name" class="form-control" value="<?php echo e($parent->parent_name); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" value="<?php echo e($parent->username); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo e($parent->email); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Update Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Mobile No</label>
                            <input type="text" name="mobile_no" class="form-control" value="<?php echo e($parent->mobile_no); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="">Select</option>
                                <option value="male" <?php echo e($parent->gender == 'male' ? 'selected' : ''); ?>>Male</option>
                                <option value="female" <?php echo e($parent->gender == 'female' ? 'selected' : ''); ?>>Female</option>
                                <option value="other" <?php echo e($parent->gender == 'other' ? 'selected' : ''); ?>>Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control" value="<?php echo e($parent->date_of_birth); ?>">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2"><?php echo e($parent->address); ?></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" value="<?php echo e($parent->city); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control" value="<?php echo e($parent->state); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Pincode</label>
                            <input type="text" name="pincode" class="form-control" value="<?php echo e($parent->pincode); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Role</label>
                            <select name="role_id" class="form-select" required>
                                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($role->id); ?>" <?php echo e($parent->role_id == $role->id ? 'selected' : ''); ?>>
                                        <?php echo e(ucfirst($role->name)); ?>
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Assign Children (Students)</label>
                            <select name="student_ids[]" class="form-select" multiple>
                                <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($student->id); ?>"
                                        <?php echo e(in_array($student->id, $assignedStudentIds ?? []) ? 'selected' : ''); ?>>
                                        <?php echo e($student->student_name); ?> (<?php echo e($student->roll_no); ?>)
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <small class="text-muted">Hold Ctrl (Windows) / Cmd (Mac) to select multiple.</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="1" <?php echo e($parent->status ? 'selected' : ''); ?>>Active</option>
                                <option value="0" <?php echo e(!$parent->status ? 'selected' : ''); ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Profile Image</label>
                            <div class="d-flex align-items-center gap-3">
                                <?php
                                    $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($parent->parent_name) . '&background=5D59E0&color=fff';
                                    if ($parent->profile_image) {
                                        $avatar = asset('uploads/parents/' . $parent->profile_image);
                                    }
                                ?>
                                <img src="<?php echo e($avatar); ?>" class="rounded-circle border" width="45" height="45" style="object-fit: cover;">
                                <input type="file" name="profile_image" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="text-end mt-5 pt-4 border-top">
                        <a href="<?php echo e(route('parents.index')); ?>" class="btn btn-light px-4 me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary-fancy px-5">Update Parent</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\pc\OneDrive\Desktop\school_lms\resources\views/admin/parents/edit.blade.php ENDPATH**/ ?>
