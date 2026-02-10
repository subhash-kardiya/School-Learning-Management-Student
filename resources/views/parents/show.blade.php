<?php $__env->startSection('title', 'Parent Profile'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid py-2">

        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="<?php echo e(route('parents.index')); ?>" class="text-muted text-decoration-none d-flex align-items-center">
                ← Back to Parents
            </a>
            <a href="<?php echo e(route('parents.edit', $parent->id)); ?>" class="btn btn-primary rounded px-4 shadow-sm">
                <i class="fa fa-pen me-1"></i> Edit Profile
            </a>
        </div>

        <div class="row g-4">

            
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 rounded p-4 d-flex flex-column align-items-center">
                    <?php
                        $img = $parent->profile_image
                            ? asset('uploads/parents/' . $parent->profile_image)
                            : 'https://ui-avatars.com/api/?name=' .
                                urlencode($parent->parent_name) .
                                '&background=6366f1&color=fff&size=200';
                    ?>
                    <img src="<?php echo e($img); ?>" class="rounded shadow-sm mb-3" width="120" height="120"
                        style="object-fit: cover; display: block;">
                    <h4 class="fw-bold mb-1 text-center"><?php echo e($parent->parent_name); ?></h4>
                    <div class="text-muted mb-2 text-center">System Identity: <?php echo e($parent->username); ?></div>

                    
                    <div class="mb-3 text-center">
                        <span class="badge bg-primary me-1">Parent</span>
                        <?php if($parent->status): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactive</span>
                        <?php endif; ?>
                    </div>

                    
                    <div class="mt-4 w-100 text-center">
                        <h6 class="text-muted fw-bold mb-2">Contact with Parents</h6>
                        <p class="mb-1"><i class="fa fa-envelope me-2 text-primary"></i><?php echo e($parent->email); ?></p>
                        <p class="mb-0"><i class="fa fa-phone me-2 text-success"></i><?php echo e($parent->mobile_no ?? 'N/A'); ?></p>
                    </div>
                </div>
            </div>

            
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 rounded p-4">

                    
                    <h5 class="text-primary fw-bold mb-3"><i class="fa fa-user me-2"></i>Personal Information</h5>
                    <div class="row mb-4">
                        <div class="col-md-4"><small
                                class="text-muted">Gender</small><br><strong><?php echo e(ucfirst($parent->gender ?? 'N/A')); ?></strong>
                        </div>
                        <div class="col-md-4"><small class="text-muted">Date of
                                Birth</small><br><strong><?php echo e($parent->date_of_birth ?? 'N/A'); ?></strong></div>
                        <div class="col-md-4"><small class="text-muted">Mobile
                                No</small><br><strong><?php echo e($parent->mobile_no ?? 'N/A'); ?></strong></div>
                        <div class="col-md-4 mt-2"><small
                                class="text-muted">City</small><br><strong><?php echo e($parent->city ?? 'N/A'); ?></strong></div>
                        <div class="col-md-4 mt-2"><small
                                class="text-muted">State</small><br><strong><?php echo e($parent->state ?? 'N/A'); ?></strong></div>
                        <div class="col-md-4 mt-2"><small
                                class="text-muted">Pincode</small><br><strong><?php echo e($parent->pincode ?? 'N/A'); ?></strong>
                        </div>
                        <div class="col-md-12 mt-2"><small
                                class="text-muted">Address</small><br><strong><?php echo e($parent->address ?? 'N/A'); ?></strong>
                        </div>
                    </div>

                    
                    <h5 class="text-primary fw-bold mb-3"><i class="fa fa-graduation-cap me-2"></i>Child Academic
                        Information</h5>
                    <?php if($parent->students && $parent->students->count()): ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Roll No</th>
                                        <th>Class</th>
                                        <th>Section</th>
                                        <th>Academic Year</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $parent->students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($student->student_name); ?></td>
                                            <td><?php echo e($student->roll_no ?? 'N/A'); ?></td>
                                            <td><?php echo e($student->class?->name ?? 'N/A'); ?></td>
                                            <td><?php echo e($student->section?->name ?? 'N/A'); ?></td>
                                            <td><?php echo e($student->academicYear?->name ?? 'N/A'); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-muted small">No students mapped to this parent.</div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\pc\OneDrive\Desktop\school_lms\resources\views/admin/parents/show.blade.php ENDPATH**/ ?>
