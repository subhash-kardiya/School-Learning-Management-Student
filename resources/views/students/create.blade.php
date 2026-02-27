<?php $__env->startSection('title', 'Student Admission'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid py-4">

        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?php echo e(route('students.index')); ?>" class="text-muted text-decoration-none">
                    <i class="fas fa-arrow-left me-1"></i> Back to Students
                </a>
            </div>
            <span class="badge bg-primary-subtle text-primary px-3 py-2">
                New Admission
            </span>
        </div>

        
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4">

                <form action="<?php echo e(route('students.store')); ?>" method="POST" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>

                    
                    <h6 class="fw-bold text-primary border-bottom pb-2 mb-4">Personal Details</h6>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Student Name</label>
                            <input type="text" name="student_name" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Roll Number</label>
                            <input type="text" name="roll_no" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Mobile No</label>
                            <input type="text" name="mobile_no" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="">Select</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control">
                        </div>

                        <div class="col-md-8">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2"></textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Pincode</label>
                            <input type="text" name="pincode" class="form-control">
                        </div>
                    </div>

                    
                    <h6 class="fw-bold text-primary border-bottom pb-2 mt-5 mb-4">Academic Details</h6>

                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Class</label>
                            <select name="class_id" id="class_id" class="form-select" required>
                                <option value="">Select Class</option>
                                <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($class->id); ?>"><?php echo e($class->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Section</label>
                            <select name="section_id" id="section_id" class="form-select" disabled required>
                                <option value="">Select Section</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Academic Year</label>
                            <select name="academic_year_id" class="form-select" required>
                                <?php $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($year->id); ?>" <?php echo e($year->is_active ? 'selected' : ''); ?>>
                                        <?php echo e($year->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Parent</label>
                            <select name="parent_id" class="form-select">
                                <option value="">Optional</option>
                                <?php $__currentLoopData = $parents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($parent->id); ?>"><?php echo e($parent->parent_name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>

                    
                    <h6 class="fw-bold text-primary border-bottom pb-2 mt-5 mb-4">System Details</h6>

                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Role</label>
                            <select name="role_id" class="form-select">
                                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($role->id); ?>" <?php echo e($role->name == 'student' ? 'selected' : ''); ?>>
                                        <?php echo e(ucfirst($role->name)); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Profile Image</label>
                            <input type="file" name="profile_image" class="form-control">
                        </div>
                    </div>

                    
                    <div class="text-end mt-5 pt-4 border-top">
                        <a href="<?php echo e(route('students.index')); ?>" class="btn btn-light px-4 me-2">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary px-5">
                            Save Student
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        $('#class_id').change(function() {
            let classId = $(this).val();

            if (classId) {
                $.get('/admin/get-sections/' + classId, function(data) {
                    $('#section_id').prop('disabled', false).empty()
                        .append('<option value="">Select Section</option>');

                    data.forEach(section => {
                        $('#section_id').append(
                            `<option value="${section.id}">${section.name}</option>`
                        );
                    });
                });
            } else {
                $('#section_id').prop('disabled', true).html('<option>Select Section</option>');
            }
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\pc\OneDrive\Desktop\school_lms\resources\views/admin/students/create.blade.php ENDPATH**/ ?>