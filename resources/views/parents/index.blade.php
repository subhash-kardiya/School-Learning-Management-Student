<?php $__env->startSection('title', 'Parents'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <p class="text-muted mb-0">Manage parent profiles and contact details</p>
            </div>
            <a href="<?php echo e(route('parents.create')); ?>" class="btn btn-primary shadow-sm px-4 py-2">
                <i class="fas fa-user-plus me-2"></i> Create Parent
            </a>
        </div>

        <div class="card glass-card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="parents-table" style="width:100%">
                        <thead class="bg-light">
                            <tr>
                                <th width="20">#</th>
                                <th>Parent</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        $(function() {
            var table = $('#parents-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "<?php echo e(route('parents.data')); ?>"
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'parent_name',
                        render: function(data, type, row) {
                            return `<div class="student-info">
                                <img src="${row.avatar}" alt="Avatar">
                                <div>
                                    <div class="fw-bold">${row.name}</div>
                                    <div class="text-muted small">${row.mobile_no ?? ''}</div>
                                </div>
                            </div>`;
                        }
                    },
                    {
                        data: 'username',
                        name: 'username',
                        render: function(data) {
                            return `<span class="username-badge">${data}</span>`;
                        }
                    },
                    {
                        data: 'email',
                        name: 'email',
                        render: function(data) {
                            return `<i class="fas fa-envelope me-1"></i>${data}`;
                        }
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-end'
                    }
                ],
                dom: '<"d-flex justify-content-between align-items-center pb-2 border-bottom"l f>rt<"d-flex justify-content-between align-items-center p-3"i p>',
                language: {
                    search: "",
                    searchPlaceholder: "Search parents...",
                    lengthMenu: "_MENU_",
                    paginate: {
                        next: '<i class="fas fa-chevron-right"></i>',
                        previous: '<i class="fas fa-chevron-left"></i>'
                    }
                },
                drawCallback: function() {
                    $('.dataTables_paginate > .pagination').addClass('mb-0');
                }
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\pc\OneDrive\Desktop\school_lms\resources\views/admin/parents/index.blade.php ENDPATH**/ ?>