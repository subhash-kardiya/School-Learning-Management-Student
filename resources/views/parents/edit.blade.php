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
            <form action="<?php echo e(route('parents.update', $parent->id)); ?>" method="POST" enctype="multipart/form-data" id="parentEditForm">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                @if ($errors->any())
                    <div class="alert alert-danger">
                        Please fix the highlighted fields and try again.
                    </div>
                @endif

                <h6 class="fw-bold text-primary border-bottom pb-2 mb-4">Parent Details</h6>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Parent Name</label>
                        <input type="text" name="parent_name"
                            class="form-control @error('parent_name') is-invalid @enderror" value="<?php echo e(old('parent_name', $parent->parent_name)); ?>">
                        @error('parent_name')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Username</label>
                        <input type="text" name="username"
                            class="form-control @error('username') is-invalid @enderror" value="<?php echo e(old('username', $parent->username)); ?>">
                        @error('username')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            value="<?php echo e(old('email', $parent->email)); ?>">
                        @error('email')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Update Password</label>
                        <input type="password" name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="Leave blank to keep current">
                        @error('password')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Mobile No</label>
                        <input type="text" name="mobile_no"
                            class="form-control @error('mobile_no') is-invalid @enderror" value="<?php echo e(old('mobile_no', $parent->mobile_no)); ?>">
                        @error('mobile_no')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                            <option value="">Select</option>
                            <option value="male" <?php echo e(old('gender', $parent->gender) == 'male' ? 'selected' : ''); ?>>Male</option>
                            <option value="female" <?php echo e(old('gender', $parent->gender) == 'female' ? 'selected' : ''); ?>>Female</option>
                            <option value="other" <?php echo e(old('gender', $parent->gender) == 'other' ? 'selected' : ''); ?>>Other</option>
                        </select>
                        @error('gender')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="date_of_birth"
                            class="form-control @error('date_of_birth') is-invalid @enderror"
                            value="<?php echo e(old('date_of_birth', $parent->date_of_birth)); ?>">
                        @error('date_of_birth')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2"><?php echo e(old('address', $parent->address)); ?></textarea>
                        @error('address')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control @error('city') is-invalid @enderror"
                            value="<?php echo e(old('city', $parent->city)); ?>">
                        @error('city')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">State</label>
                        <input type="text" name="state" class="form-control @error('state') is-invalid @enderror"
                            value="<?php echo e(old('state', $parent->state)); ?>">
                        @error('state')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Pincode</label>
                        <input type="text" name="pincode"
                            class="form-control @error('pincode') is-invalid @enderror" value="<?php echo e(old('pincode', $parent->pincode)); ?>">
                        @error('pincode')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Role</label>
                        <select name="role_id" class="form-select @error('role_id') is-invalid @enderror">
                            <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($role->id); ?>" <?php echo e((int) old('role_id', $parent->role_id) === (int) $role->id ? 'selected' : ''); ?>>
                                <?php echo e(ucfirst($role->name)); ?>
                            </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        @error('role_id')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Assign Children (Students)</label>
                        <?php $selectedStudents = old('student_ids', $assignedStudentIds ?? []); ?>
                        <select name="student_ids[]" class="form-select @error('student_ids') is-invalid @enderror"
                            multiple>
                            <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($student->id); ?>" <?php echo e(in_array($student->id, $selectedStudents) ? 'selected' : ''); ?>>
                                <?php echo e($student->student_name); ?> (<?php echo e($student->roll_no); ?>)
                            </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <small class="text-muted">Hold Ctrl (Windows) / Cmd (Mac) to select multiple.</small>
                        @error('student_ids')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="1" <?php echo e((string) old('status', (string) $parent->status) === '1' ? 'selected' : ''); ?>>Active</option>
                            <option value="0" <?php echo e((string) old('status', (string) $parent->status) === '0' ? 'selected' : ''); ?>>Inactive</option>
                        </select>
                        @error('status')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
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
                            <img src="<?php echo e($avatar); ?>" class="rounded-circle border" width="45"
                                height="45" style="object-fit: cover;">
                            <input type="file" name="profile_image"
                                class="form-control @error('profile_image') is-invalid @enderror">
                        </div>
                        @error('profile_image')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
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

<?php $__env->startPush('scripts'); ?>
<script>
    (function() {
        $('#parentEditForm').on('submit', function(e) {
            $('.client-error').remove();
            let hasError = false;
            const requiredFields = [
                'parent_name', 'username', 'email', 'mobile_no', 'gender',
                'date_of_birth', 'address', 'city', 'state', 'pincode',
                'role_id', 'status'
            ];

            requiredFields.forEach(function(name) {
                const $field = $('[name="' + name + '"]');
                if (!$field.length) return;

                const isEmpty = String($field.val() || '').trim() === '';
                if (isEmpty) {
                    hasError = true;
                    $field.addClass('is-invalid');
                    $field.after(
                        '<span class="text-danger d-block client-error">This field is required.</span>'
                    );
                } else {
                    $field.removeClass('is-invalid');
                }
            });

            if (hasError) {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }
        });
    })();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\pc\OneDrive\Desktop\school_lms\resources\views/admin/parents/edit.blade.php ENDPATH**/ ?>
