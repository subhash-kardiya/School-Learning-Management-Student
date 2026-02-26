<?php $__env->startSection('title', 'Edit Role'); ?>



<?php $__env->startSection('content'); ?>
    <div class="container-fluid py-4 role-ui">

        <!-- PAGE HEADER -->
        <div class="page-header mb-4 d-flex justify-content-between align-items-center">
            <div>
                <a href="<?php echo e(route('roles.index')); ?>" class="btn btn-link link-secondary text-decoration-none p-0 mb-1">
                    <i class="fas fa-arrow-left me-1"></i> Back to Roles
                </a>
                <p class="text-muted mb-0">Modify role details and assign permissions</p>
            </div>
            <div>
                <button type="submit" form="editRoleForm" class="btn btn-primary-fancy px-4">
                    <i class="fas fa-save me-1"></i> Save Changes
                </button>
            </div>
        </div>

        <form action="<?php echo e(route('roles.update', $role->id)); ?>" method="POST" id="editRoleForm">
            <?php echo method_field('PUT'); ?>
            <?php echo csrf_field(); ?>

            <?php if($errors->any()): ?>
                <div class="alert alert-danger border-0 shadow-sm mb-4">
                    <ul class="mb-0 small">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="glass-card mb-4">
                <h6 class="section-title mb-4">Role Details</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Role Name</label>
                        <input type="text" name="name" class="form-control" value="<?php echo e($role->name); ?>"
                            placeholder="e.g. Administrator" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control" value="<?php echo e($role->description); ?>"
                            placeholder="Brief role summary">
                    </div>
                </div>
            </div>

            <div class="glass-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="section-title m-0">Manage Permissions</h6>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="selectAllPermissions">
                        <label class="form-check-label" for="selectAllPermissions">Select All</label>
                    </div>
                </div>

                <div class="accordion" id="permissionsAccordion">
                    <?php
                        $groupedPermissions = $permissions->groupBy(function ($item, $key) {
                            if (is_string($item->name) && str_contains($item->name, '_')) {
                                $parts = explode('_', $item->name);
                                return $parts[0]; // Group by resource, e.g., "student"
                            }
                            return 'other'; // Default group for permissions without an underscore
                        });
                    ?>

                    <?php $__currentLoopData = $groupedPermissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group => $perms): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading-<?php echo e($group); ?>">
                                <button class="accordion-button <?php echo e($loop->first ? '' : 'collapsed'); ?>" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo e($group); ?>"
                                    aria-expanded="<?php echo e($loop->first ? 'true' : 'false'); ?>"
                                    aria-controls="collapse-<?php echo e($group); ?>">
                                    <?php echo e(ucfirst($group)); ?> Management
                                </button>
                            </h2>
                            <div id="collapse-<?php echo e($group); ?>"
                                class="accordion-collapse collapse <?php echo e($loop->first ? 'show' : ''); ?>"
                                aria-labelledby="heading-<?php echo e($group); ?>" data-bs-parent="#permissionsAccordion">
                                <div class="accordion-body">
                                    <div class="row g-2">
                                        <?php $__currentLoopData = $perms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="col-md-3">
                                                <label class="perm-chip w-100 text-center">
                                                    <input type="checkbox" name="permissions[]"
                                                        value="<?php echo e($permission->id); ?>" class="permission-checkbox"
                                                        <?php echo e(in_array($permission->id, $rolePermissions) ? 'checked' : ''); ?>>
                                                    <span><?php echo e(ucwords(str_replace('_', ' ', $permission->name))); ?></span>
                                                </label>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </form>

    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        $(function() {
            // "Select All" functionality
            $('#selectAllPermissions').on('click', function() {
                $('.permission-checkbox').prop('checked', $(this).prop('checked'));
            });

            // Check if all are checked initially to set Select All state
            if ($('.permission-checkbox:checked').length === $('.permission-checkbox').length) {
                $('#selectAllPermissions').prop('checked', true);
            }

            // Update Select All when individual checkboxes change
            $('.permission-checkbox').on('change', function() {
                if ($('.permission-checkbox:checked').length === $('.permission-checkbox').length) {
                    $('#selectAllPermissions').prop('checked', true);
                } else {
                    $('#selectAllPermissions').prop('checked', false);
                }
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\pc\OneDrive\Desktop\school_lms\resources\views/admin/roles/edit.blade.php ENDPATH**/ ?>