<?php $__env->startSection('title', 'Section Details'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid py-4 section-module-compact">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?php echo e(route('section.index')); ?>" class="btn btn-link text-decoration-none text-muted p-0 mb-2">
                    <i class="fas fa-arrow-left me-1"></i> Back to Sections
                </a>
@push('css')
    <link rel="stylesheet" href="{{ asset('css/resize/section-compact.css') }}">
@endpush
</div>
            <div class="d-flex gap-2">
                <a href="<?php echo e(route('section.edit', $section->id)); ?>" class="btn btn-primary-fancy shadow-sm">
                    <i class="fa fa-pen me-2"></i> Edit Section
                </a>
            </div>
        </div>

        <!-- Info Card -->
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-0">
                <h5 class="mb-0 fw-bold">Section Information</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">

                    <!-- Section Name -->
                    <div class="col-md-4">
                        <label class="form-label text-muted">Section Name</label>
                        <div
                            class="p-3 bg-light rounded-3 border-start border-4 border-primary shadow-sm hover-shadow hover-scale transition">
                            <span class="fw-bold text-dark fs-5"><?php echo e($section->name); ?></span>
                        </div>
                    </div>

                    <!-- Class Name -->
                    <div class="col-md-4">
                        <label class="form-label text-muted">Class Name</label>
                        <div
                            class="p-3 bg-light rounded-3 border-start border-4 border-info shadow-sm hover-shadow hover-scale transition">
                            <span class="fw-bold text-primary fs-5"><?php echo e($section->class->name ?? 'N/A'); ?></span>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="col-md-4">
                        <label class="form-label text-muted">Status</label>
                        <div>
                            <?php if($section->status == 1): ?>
                                <span
                                    class="badge bg-success text-white rounded-pill fw-semibold px-3 py-2 shadow-sm hover-scale transition">
                                    Active
                                </span>
                            <?php else: ?>
                                <span
                                    class="badge bg-danger text-white rounded-pill fw-semibold px-3 py-2 shadow-sm hover-scale transition">
                                    Inactive
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Capacity -->
                    <div class="col-md-4">
                        <label class="form-label text-muted">Capacity</label>
                        <div
                            class="p-3 bg-light rounded-3 border-start border-4 border-warning shadow-sm hover-shadow hover-scale transition">
                            <span class="fw-bold text-dark fs-5">{{ $section->capacity ?? 'N/A' }}</span>
                        </div>
                    </div>

                    <!-- Created At -->
                    <div class="col-md-6 mt-4">
                        <label class="form-label text-muted">Created At</label>
                        <div
                            class="p-3 bg-light rounded-3 border-start border-4 border-secondary shadow-sm hover-shadow hover-scale transition small text-muted">
                            <?php echo e($section->created_at ? $section->created_at->format('d M Y, h:i A') : 'N/A'); ?>

                        </div>
                    </div>

                    <!-- Updated At -->
                    <div class="col-md-6 mt-4">
                        <label class="form-label text-muted">Updated At</label>
                        <div
                            class="p-3 bg-light rounded-3 border-start border-4 border-secondary shadow-sm hover-shadow hover-scale transition small text-muted">
                            <?php echo e($section->updated_at ? $section->updated_at->format('d M Y, h:i A') : 'N/A'); ?>

                        </div>
                    </div>
                </div>

                <!-- Delete Action -->
                <div class="mt-5 pt-4 border-top d-flex justify-content-between align-items-center">
                    <div class="text-muted small">Record initialized on <?php echo e($section->created_at->format('M d, Y')); ?></div>
                    <form action="<?php echo e(route('section.destroy', $section->id)); ?>" method="POST"
                        onsubmit="return confirm('Are you sure you want to delete this section?')">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-danger px-4 shadow-sm hover-scale transition">
                            <i class="fa fa-trash me-1"></i> Delete Section
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Extra CSS for interactive effects -->

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\pc\OneDrive\Desktop\school_lms\resources\views/admin/section/show.blade.php ENDPATH**/ ?>
