<?php $__env->startSection('title', 'Roles & Permissions'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid py-4 role-ui">

        <!-- PAGE HEADER -->
        <div class="page-header mb-4 d-flex justify-content-between align-items-center">
            <div>
                <p class="text-muted mb-0">Manage access control & security policies</p>
            </div>
            <button class="btn btn-primary-fancy" data-bs-toggle="collapse" data-bs-target="#roleForm">
                <i class="fas fa-plus me-1"></i> New Role
            </button>
        </div>

        <!-- CREATE ROLE -->
        <div class="collapse <?php echo e($errors->any() ? 'show' : ''); ?> mb-4" id="roleForm">
            <div class="glass-card">
                <h6 class="section-title">Create a New Role</h6>

                <form action="<?php echo e(route('roles.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Role Name</label>
                            <input type="text" name="name" class="form-control"
                                placeholder="e.g., Administrator, Teacher" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" class="form-control"
                                placeholder="Brief summary of the role">
                        </div>
                    </div>

                    <!-- PERMISSIONS -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-semibold m-0">Assign Permissions</h6>
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
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse-<?php echo e($group); ?>" aria-expanded="false"
                                        aria-controls="collapse-<?php echo e($group); ?>">
                                        <?php echo e(ucfirst($group)); ?> Management
                                    </button>
                                </h2>
                                <div id="collapse-<?php echo e($group); ?>" class="accordion-collapse collapse"
                                    aria-labelledby="heading-<?php echo e($group); ?>" data-bs-parent="#permissionsAccordion">
                                    <div class="accordion-body">
                                        <div class="row g-2">
                                            <?php $__currentLoopData = $perms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="col-md-3">
                                                    <label class="perm-chip">
                                                        <input type="checkbox" name="permissions[]"
                                                            value="<?php echo e($permission->id); ?>" class="permission-checkbox">
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

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary-fancy px-5">
                            <i class="fas fa-save me-1"></i> Save Role
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ROLES TABLE -->
        <div class="glass-card">
            <h6 class="section-title mb-3">Existing Roles</h6>

            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0" id="roles-table">
                    <thead>
                        <tr>
                            <th width="60">#</th>
                            <th>Role</th>
                            <th>Permissions</th>
                            <th class="text-end" width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

    </div>
<?php $__env->stopSection(); ?>


<?php $__env->startPush('scripts'); ?>
    <script>
        $(function() {
            // "Select All" permissions functionality
            $('#selectAllPermissions').on('click', function() {
                $('.permission-checkbox').prop('checked', $(this).prop('checked'));
            });

            var table = $('#roles-table').DataTable({
                processing: true,
                serverSide: true,
                dom: '<"d-flex justify-content-between align-items-center pb-3 border-bottom"l f>rt<"d-flex justify-content-between align-items-center p-4"i p>',
                ajax: "<?php echo e(route('roles.index')); ?>",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'permissions',
                        name: 'permissions',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            if (Array.isArray(data) && data.length > 0) {
                                let limit = 4;
                                let displayed = data.slice(0, limit).map(p => {
                                    return `<span class="permission-pill">${p.name.replace(/_/g, ' ')}</span>`;
                                }).join('');

                                if (data.length > limit) {
                                    displayed +=
                                        `<span class="badge bg-secondary rounded-pill ms-1">+${data.length - limit} more</span>`;
                                }
                                return displayed;
                            }
                            return '<span class="text-muted small">No permissions assigned</span>';
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-end'
                    }
                ],
                language: {
                    search: "",
                    searchPlaceholder: "Search roles...",
                    lengthMenu: "Show _MENU_ entries",
                    processing: '<div class="d-flex justify-content-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>',
                    paginate: {
                        next: '<i class="fas fa-chevron-right"></i>',
                        previous: '<i class="fas fa-chevron-left"></i>'
                    }
                },
                initComplete: function() {
                    $('.dataTables_length select').removeClass('form-select-sm');
                    $('.dataTables_filter input').removeClass().addClass(
                        'form-control form-control-sm');
                },
                drawCallback: function() {
                    $('.dataTables_paginate > .pagination').addClass('mb-0');
                }
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\pc\OneDrive\Desktop\school_lms\resources\views/admin/roles/index.blade.php ENDPATH**/ ?>