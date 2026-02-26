<?php $__env->startSection('title', 'Faculty Directory'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid py-4">

        <!-- PAGE HEADER -->
        <div class="d-flex align-items-center mb-4">
            <div>
                <p class="page-subtitle">Manage professional profiles and teaching assignments</p>
            </div>
            <div class="ms-auto">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('teacher_add')): ?>
                    <a href="<?php echo e(route('teachers.create')); ?>" class="btn btn-primary-fancy">
                        <i class="fas fa-user-plus me-2"></i> Add Faculty
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- FILTER BAR -->
        <div class="admin-filter-bar mb-3">
            <div class="d-flex align-items-center gap-2">
                <label for="filter-status" class="mb-0 fw-semibold">Filter:</label>
                <select class="form-select form-select-sm" id="filter-status">
                    <option value="">All Status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
        </div>

        <!-- TABLE -->
        <div class="card admin-card">
            <div class="card-body p-0">
                <table class="table admin-table align-middle mb-0 w-100" id="teachers-table">
                    <thead>
                        <tr>
                            <th width="10">#</th>
                            <th>Faculty</th>
                            <th width="160">Username</th>
                            <th>Email</th>
                            <th width="120">Status</th>
                            <th width="150" class="text-end">Actions</th>
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
        $(document).ready(function() {

            let table = $('#teachers-table').DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                ajax: {
                    url: "<?php echo e(route('teachers.index')); ?>",
                    type: "GET",
                    data: function(d) {
                        d.status = $('#filter-status').val(); // Send status filter to server
                    }
                },

                // Clean ERP-style layout
                dom: '<"d-flex justify-content-between align-items-center p-3 border-bottom"' +
                    '<"d-flex align-items-center gap-2"l>' +
                    '<"ms-auto"f>' +
                    '>' +
                    'rt' +
                    '<"d-flex justify-content-between align-items-center p-3 border-top"ip>',

                columns: [{
                        data: 'DT_RowIndex',
                        searchable: false
                    },
                    {
                        data: 'teacher_info',
                        name: 'name'
                    },
                    {
                        data: 'username',
                        name: 'username'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'status',
                        searchable: false
                    },
                    {
                        data: 'action',
                        searchable: false,
                        className: 'text-end'
                    },
                ],

                language: {
                    search: "",
                    searchPlaceholder: "Search name, username, or email...",
                    lengthMenu: "_MENU_",
                    paginate: {
                        next: '<i class="fas fa-chevron-right"></i>',
                        previous: '<i class="fas fa-chevron-left"></i>'
                    }
                },

                drawCallback: function() {
                    $('.dataTables_paginate > .pagination')
                        .addClass('pagination-sm mb-0');
                }
            });

            // Filter change
            $('#filter-status').on('change', function() {
                table.draw();
            });

        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\pc\OneDrive\Desktop\school_lms\resources\views/admin/teachers/index.blade.php ENDPATH**/ ?>