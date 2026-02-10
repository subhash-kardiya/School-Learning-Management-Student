<?php $__env->startSection('title', 'Create Parent'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid py-4">
        <div class="mb-4">
            <a href="<?php echo e(route('parents.index')); ?>" class="text-muted text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i> Back to Parents
            </a>
        </div>

        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4">
                <form action="<?php echo e(route('parents.store')); ?>" method="POST" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>

                    <h6 class="fw-bold text-primary border-bottom pb-2 mb-4">Parent Details</h6>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Parent Name</label>
                            <input type="text" name="parent_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Mobile No</label>
                            <input type="text" name="mobile_no" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="">Select</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Pincode</label>
                            <input type="text" name="pincode" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Role</label>
                            <select name="role_id" class="form-select" required>
                                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($role->id); ?>" <?php echo e((isset($parentRole) && $parentRole && $parentRole->id == $role->id) ? 'selected' : ''); ?>>
                                        <?php echo e(ucfirst($role->name)); ?>
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Assign Children (Students)</label>
                            <select name="student_ids[]" class="form-select" multiple>
                                <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($student->id); ?>">
                                        <?php echo e($student->student_name); ?> (<?php echo e($student->roll_no); ?>)
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <small class="text-muted">Hold Ctrl (Windows) / Cmd (Mac) to select multiple.</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Profile Image</label>
                            <input type="file" name="profile_image" class="form-control">
                        </div>
                    </div>

                    <div class="text-end mt-5 pt-4 border-top">
                        <a href="<?php echo e(route('parents.index')); ?>" class="btn btn-light px-4 me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary px-5">Save Parent</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\pc\OneDrive\Desktop\school_lms\resources\views/admin/parents/create.blade.php ENDPATH**/ ?>
