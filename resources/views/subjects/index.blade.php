<?php $__env->startSection('title', 'Subjects Management'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid py-4">

        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <p class="text-muted mb-0">Manage school subjects, course codes, and teacher assignments</p>
            </div>
            <button type="button" class="btn btn-primary-fancy" data-bs-toggle="collapse" data-bs-target="#subjectForm">
                <i class="fa fa-plus me-2"></i> Add New Subject
            </button>
        </div>

        <!-- Collapsible Form -->
        <div class="collapse <?php echo e($errors->any() ? 'show' : ''); ?> mb-4" id="subjectForm">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Create New Subject</h5>
                </div>
                <div class="card-body p-4">
                    <form action="<?php echo e(route('subjects.store')); ?>" method="POST">
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

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Subject Name</label>
                                <input type="text" name="name" class="form-control" placeholder="e.g. Mathematics"
                                    value="<?php echo e(old('name')); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Subject Code</label>
                                <input type="text" name="subject_code" class="form-control" placeholder="e.g. MATH101"
                                    value="<?php echo e(old('subject_code')); ?>" required>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Assign Class</label>
                                <select name="class_id" class="form-select" required>
                                    <option value="">Select Class</option>
                                    <?php $__currentLoopData = \App\Models\Classes::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($class->id); ?>"
                                            <?php echo e(old('class_id') == $class->id ? 'selected' : ''); ?>>
                                            <?php echo e($class->name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Assign Teacher</label>
                                <select name="teacher_id" class="form-select" required>
                                    <option value="">Select Teacher</option>
                                    <?php $__currentLoopData = \App\Models\Teacher::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($teacher->id); ?>"
                                            <?php echo e(old('teacher_id') == $teacher->id ? 'selected' : ''); ?>>
                                            <?php echo e($teacher->name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary-fancy px-5">Save Subject Details</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Subject Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Existing Subjects</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="subjects-table" style="width:100%">
                        <thead>
                            <tr>
                                <th width="60">No</th>
                                <th>Subject Name</th>
                                <th>Course Code</th>
                                <th>Class</th>
                                <th>Assigned Teacher</th>
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
            $('#subjects-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '<?php echo e(route('subjects.index')); ?>',
                dom: '<"d-flex justify-content-between align-items-center p-2 border-bottom"l f>rt<"d-flex justify-content-between align-items-center p-4"i p>',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'subject_code',
                        name: 'subject_code'
                    },
                    {
                        data: 'class',
                        name: 'class'
                    },
                    {
                        data: 'teacher',
                        name: 'teacher'
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
                    },
                ],
                language: {
                    search: "",
                    searchPlaceholder: "Search subjects...",
                    lengthMenu: "_MENU_",
                    processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                    paginate: {
                        next: '<i class="fas fa-chevron-right"></i>',
                        previous: '<i class="fas fa-chevron-left"></i>'
                    }
                },
                drawCallback: function() {
                    $('.dataTables_paginate > .pagination').addClass('mb-0');
                }
            });
            $('.dataTables_length select').addClass('form-select-sm');
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\pc\OneDrive\Desktop\school_lms\resources\views/admin/subject/index.blade.php ENDPATH**/ ?>