<?php $__env->startSection('title', 'Edit Teacher Profile'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-2 py-2 teacher-module-compact" style="min-height:100vh;">

    <!-- Header -->
    <div class="mb-4">
        <a href="<?php echo e(route('teachers.index')); ?>" class="btn btn-link text-decoration-none text-muted mb-2">
            <i class="fas fa-arrow-left me-1"></i> Back to Faculty Directory
        </a>
    </div>

    <!-- Full-Width Form -->
    <div class="row">
        <div class="col-12">
            <div class="card p-4 shadow-sm rounded-4 bg-white form-card">

            @if ($errors->any())
                <div class="alert alert-danger">
                    Please fix the highlighted fields and try again.
                </div>
            @endif

            <form action="<?php echo e(route('teachers.update', $teacher->id)); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <!-- Professional Identity -->
                    <h5 class="text-primary fw-bold mb-3">Professional Identity</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="<?php echo e(old('name', $teacher->name)); ?>" placeholder="e.g. Dr. Sarah Smith">
                            @error('name')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">System Username</label>
                            <input type="text" name="username"
                                class="form-control @error('username') is-invalid @enderror"
                                value="<?php echo e(old('username', $teacher->username)); ?>" placeholder="Unique username">
                            @error('username')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                value="<?php echo e(old('email', $teacher->email)); ?>" placeholder="e.g. sarah@school.edu">
                            @error('email')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Security Password</label>
                            <input type="password" name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="Leave blank to keep current">
                            @error('password')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Contact Number</label>
                            <input type="text" name="mobile_no"
                                class="form-control @error('mobile_no') is-invalid @enderror"
                                value="<?php echo e(old('mobile_no', $teacher->mobile_no)); ?>" placeholder="e.g. 01234 56789">
                            @error('mobile_no')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Gender Identity</label>
                            <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                                <option value="">Select Gender</option>
                                <option value="male" <?php echo e(old('gender', $teacher->gender) == 'male' ? 'selected' : ''); ?>>Male</option>
                                <option value="female" <?php echo e(old('gender', $teacher->gender) == 'female' ? 'selected' : ''); ?>>Female
                                </option>
                                <option value="other" <?php echo e(old('gender', $teacher->gender) == 'other' ? 'selected' : ''); ?>>Other
                                </option>
                            </select>
                            @error('gender')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                <!-- Employment & Address -->
                    <h5 class="text-primary fw-bold mt-5 mb-3">Personal & Employment Details</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth"
                                class="form-control @error('date_of_birth') is-invalid @enderror"
                                value="<?php echo e(old('date_of_birth', $teacher->date_of_birth)); ?>">
                            @error('date_of_birth')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Join Date</label>
                            <input type="date" name="join_date"
                                class="form-control @error('join_date') is-invalid @enderror"
                                value="<?php echo e(old('join_date', $teacher->join_date)); ?>">
                            @error('join_date')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Residential Address</label>
                            <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2"
                                placeholder="Street, Apartment, Unit"><?php echo e(old('address', $teacher->address)); ?></textarea>
                            @error('address')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control @error('city') is-invalid @enderror"
                                value="<?php echo e(old('city', $teacher->city)); ?>" placeholder="e.g. New York">
                            @error('city')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">State / Province</label>
                            <input type="text" name="state" class="form-control @error('state') is-invalid @enderror"
                                value="<?php echo e(old('state', $teacher->state)); ?>" placeholder="e.g. NY">
                            @error('state')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Pincode</label>
                            <input type="text" name="pincode"
                                class="form-control @error('pincode') is-invalid @enderror"
                                value="<?php echo e(old('pincode', $teacher->pincode)); ?>" placeholder="e.g. 10001">
                            @error('pincode')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Highest Qualification</label>
                            <input type="text" name="qualification"
                                class="form-control @error('qualification') is-invalid @enderror"
                                value="<?php echo e(old('qualification', $teacher->qualification)); ?>"
                                placeholder="e.g. PhD in Education">
                            @error('qualification')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Teaching Experience (Years)</label>
                            <input type="number" name="exp" class="form-control @error('exp') is-invalid @enderror"
                                value="<?php echo e(old('exp', $teacher->exp)); ?>" placeholder="e.g. 5">
                            @error('exp')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="1" <?php echo e(old('status', $teacher->status) == 1 ? 'selected' : ''); ?>>Active</option>
                                <option value="0" <?php echo e(old('status', $teacher->status) == 0 ? 'selected' : ''); ?>>Inactive</option>
                            </select>
                            @error('status')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Profile Avatar</label>
                            <input type="file" name="profile_image"
                                class="form-control mt-2 @error('profile_image') is-invalid @enderror">
                            @error('profile_image')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                <!-- Submit -->
                <div class="text-end mt-5">
                    <a href="<?php echo e(route('teachers.index')); ?>" class="btn btn-secondary me-2 rounded">Cancel</a>
                    <button type="submit" class="btn btn-primary rounded px-5">Update Faculty</button>
                </div>
            </form>
            </div>
        </div>
    </div>


</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('css'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/resize/teacher-compact.css')); ?>">
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\pc\OneDrive\Desktop\school_lms\resources\views/admin/teachers/edit.blade.php ENDPATH**/ ?>
