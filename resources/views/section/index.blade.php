<?php $__env->startSection('title', 'Sections Management'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid py-4">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <p class="text-muted small mb-0">Manage class sections and seating capacity allocations</p>
            </div>
            <button type="button" class="btn btn-primary-fancy" data-bs-toggle="collapse" data-bs-target="#sectionForm">
                <i class="fa fa-plus me-2"></i> Create New Section
            </button>
        </div>

        <!-- Collapsible Add Form -->
        <div class="collapse <?php echo e($errors->any() ? 'show' : ''); ?> mb-4" id="sectionForm">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Create New Section</h5>
                </div>
                <div class="card-body p-4">
                    <form action="<?php echo e(route('section.store')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <div class="row g-4 mb-4">
                            <div class="col-md-4">
                                <label for="name" class="form-label">Section Name</label>
                                <input type="text" class="form-control" name="name" placeholder="e.g. Section A"
                                    required>
                            </div>

                            <div class="col-md-4">
                                <label for="class_id" class="form-label">Assign Class</label>
                                <select name="class_id" class="form-select" required>
                                    <option value="">Select Class</option>
                                    <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($class->id); ?>"><?php echo e($class->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="capacity" class="form-label">Capacity</label>
                                <input type="number" class="form-control" name="capacity" min="1"
                                    placeholder="e.g. 40" required>
                            </div>
                            <div class="col-md-4">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary-fancy px-5">Save Section Details</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sections Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Existing Sections</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="sections-table" style="width:100%">
                        <thead>
                            <tr>
                                <th width="60">No</th>
                                <th>Section Name</th>
                                <th>Class Name</th>
                                <th>Status</th>
                                <th>Capacity</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script type="text/javascript">
        $(function() {
            var table = $('#sections-table').DataTable({
                processing: true,
                serverSide: true,
                dom: '<"d-flex justify-content-between align-items-center p-2 border-bottom"l f>rt<"d-flex justify-content-between align-items-center p-4"i p>',
                ajax: "<?php echo e(route('section.index')); ?>",
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
                        data: 'class_name',
                        name: 'class_name'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'capacity',
                        name: 'capacity'
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
                    searchPlaceholder: "Search sections...",
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

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\pc\OneDrive\Desktop\school_lms\resources\views/admin/section/index.blade.php ENDPATH**/ ?>
