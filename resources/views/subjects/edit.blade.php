<?php $__env->startSection('title', 'Edit Subject'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4 subject-module-compact">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="<?php echo e(route('subjects.index')); ?>" class="btn btn-link text-decoration-none text-muted p-0 mb-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Subjects List
            </a>
        </div>
    </div>

    <!-- Edit Form Card -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0 fw-bold">Subject Information</h5>
                </div>
                <div class="card-body p-4">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            Please fix the highlighted fields and try again.
                        </div>
                    @endif
                    <form action="<?php echo e(route('subjects.update', $subject->id)); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>



                        <!-- Subject Name & Code -->
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-semibold">Subject Name</label>
                                <input type="text" name="name" id="name"
                                    class="form-control shadow-sm @error('name') is-invalid @enderror"
                                    placeholder="e.g. Mathematics" value="<?php echo e(old('name', $subject->name)); ?>">
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="subject_code" class="form-label fw-semibold">Course Code</label>
                                <input type="text" name="subject_code" id="subject_code"
                                    class="form-control shadow-sm @error('subject_code') is-invalid @enderror"
                                    placeholder="e.g. MATH101" value="<?php echo e(old('subject_code', $subject->subject_code)); ?>">
                                @error('subject_code')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- Class -->
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="class_id" class="form-label fw-semibold">Assign Class</label>
                                <select name="class_id" id="class_id"
                                    class="form-select shadow-sm @error('class_id') is-invalid @enderror">
                                    <option value="">Select Class</option>
                                    <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($class->id); ?>" <?php echo e(old('class_id', $subject->class_id) == $class->id ? 'selected' : ''); ?>>
                                        <?php echo e($class->name); ?>

                                    </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                @error('class_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="status" class="form-label fw-semibold">Subject Status</label>
                                <select name="status" id="status"
                                    class="form-select shadow-sm @error('status') is-invalid @enderror">
                                    <option value="1" <?php echo e(old('status', $subject->status) == 1 ? 'selected' : ''); ?>>Active</option>
                                    <option value="0" <?php echo e(old('status', $subject->status) == 0 ? 'selected' : ''); ?>>Inactive</option>
                                </select>
                                @error('status')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-end gap-2 border-top pt-4">
                            <a href="<?php echo e(route('subjects.index')); ?>" class="btn btn-light px-4 shadow-sm"
                                style="border-radius:10px;">Discard Changes</a>
                            <button type="submit" class="btn btn-primary-fancy px-5 shadow-sm">Update Subject</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('css'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/resize/subject-compact.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\pc\OneDrive\Desktop\school_lms\resources\views/admin/subject/edit.blade.php ENDPATH**/ ?>
