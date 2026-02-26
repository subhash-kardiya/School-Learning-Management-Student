<?php $__env->startSection('title', 'Academic Years'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid py-3 academic-white">

        <!-- PAGE HEADER -->
        <div class="page-header mb-4">
            <div>

                <p class="page-subtitle">
                    Define academic years used across admissions, attendance, exams and reports
                </p>
            </div>

            <button class="btn btn-primary-fancy" data-bs-toggle="collapse" data-bs-target="#academicYearForm">
                <i class="fa fa-plus me-2"></i> Add Academic Year
            </button>
        </div>

        <!-- SUCCESS MESSAGE -->
        <?php if(session('success')): ?>
            <div class="alert alert-success border-0 shadow-sm mb-3">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="alert alert-danger border-0 shadow-sm mb-3">
                <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>

        <!-- CREATE ACADEMIC YEAR -->
        <div class="collapse <?php echo e($errors->any() ? 'show' : ''); ?>" id="academicYearForm">
            <div class="card mb-4">
                <div class="card-header">
                    <strong>Create Academic Year</strong>
                </div>

                <div class="card-body">
                    <form action="<?php echo e(route('academic.year.store')); ?>" method="POST">
                        <?php echo csrf_field(); ?>

                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label">Academic Year</label>
                                <input type="text" name="name" class="form-control" placeholder="2025 - 2026"
                                    required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control" required>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary-fancy px-4">
                                Save Academic Year
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- LIST -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Academic Years</strong>
                <span class="badge badge-soft-info">
                    Used system-wide
                </span>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive-md">
                    <table class="table table-hover align-middle mb-0" id="academic-years-table">
                        <thead>
                            <tr>
                                <th width="20">ID</th>
                                <th>Academic Year</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th>Actions</th>
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
            $('#academic-years-table').DataTable({
                processing: true,
                serverSide: true,
                dom: '<"d-flex justify-content-between align-items-center p-2 border-bottom"l f>' +
                    'rt' +
                    '<"d-flex justify-content-between align-items-center p-2"i p>',
                ajax: "<?php echo e(route('academic.year.index')); ?>",
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'start_date'
                    },
                    {
                        data: 'end_date'
                    },
                    {
                        data: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-end'
                    }
                ],
                language: {
                    search: "",
                    searchPlaceholder: "Search academic years...",
                    lengthMenu: "_MENU_",
                    processing: '<div class="spinner-border text-primary" role="status"></div>',
                    paginate: {
                        next: '<i class="fas fa-chevron-right"></i>',
                        previous: '<i class="fas fa-chevron-left"></i>'
                    }
                },
                drawCallback: function() {
                    $('.dataTables_paginate > .pagination').addClass('mb-0');
                    $('.dataTables_filter input').addClass('form-control-sm');
                }
            });

            $('.dataTables_length select').addClass('form-select-sm');
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\pc\OneDrive\Desktop\school_lms\resources\views/admin/academic-year/index.blade.php ENDPATH**/ ?>
