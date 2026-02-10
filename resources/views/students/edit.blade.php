<?php $__env->startSection('title', 'Edit Student Profile'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid py-4">

        
        <div class="mb-4 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
            <div>
                <a href="<?php echo e(route('students.index')); ?>" class="btn btn-link text-muted p-0 mb-2 text-decoration-none">
                    <i class="fas fa-arrow-left me-1"></i> Back to Student Directory
                </a>
            </div>
        </div>

        
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form action="<?php echo e(route('students.update', $student->id)); ?>" method="POST" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>

                    
                    <h5 class="text-primary fw-bold mb-3 border-bottom pb-2"><i class="fas fa-user me-2"></i>Personal
                        Details</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Student Name</label>
                            <input type="text" name="student_name" class="form-control rounded-3"
                                value="<?php echo e($student->student_name); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Roll Number</label>
                            <input type="text" name="roll_no" class="form-control rounded-3"
                                value="<?php echo e($student->roll_no); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Username</label>
                            <input type="text" name="username" class="form-control rounded-3"
                                value="<?php echo e($student->username); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control rounded-3"
                                value="<?php echo e($student->email); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Password</label>
                            <input type="password" name="password" class="form-control rounded-3"
                                placeholder="Leave blank to keep current">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Mobile Number</label>
                            <input type="text" name="mobile_no" class="form-control rounded-3"
                                value="<?php echo e($student->mobile_no); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Gender</label>
                            <select name="gender" class="form-select rounded-3">
                                <option value="male" <?php echo e($student->gender == 'male' ? 'selected' : ''); ?>>Male</option>
                                <option value="female" <?php echo e($student->gender == 'female' ? 'selected' : ''); ?>>Female</option>
                                <option value="other" <?php echo e($student->gender == 'other' ? 'selected' : ''); ?>>Other</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control rounded-3"
                                value="<?php echo e($student->date_of_birth); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Address</label>
                            <input type="text" name="address" class="form-control rounded-3"
                                value="<?php echo e($student->address); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">City</label>
                            <input type="text" name="city" class="form-control rounded-3"
                                value="<?php echo e($student->city); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">State</label>
                            <input type="text" name="state" class="form-control rounded-3"
                                value="<?php echo e($student->state); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Pincode</label>
                            <input type="text" name="pincode" class="form-control rounded-3"
                                value="<?php echo e($student->pincode); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Profile Image</label>
                            <div class="d-flex align-items-center gap-3">
                                <?php
                                    $avatar = $student->profile_image
                                        ? asset('uploads/students/' . $student->profile_image)
                                        : 'https://ui-avatars.com/api/?name=' .
                                            urlencode($student->student_name) .
                                            '&background=5D59E0&color=fff';
                                ?>
                                <img src="<?php echo e($avatar); ?>" class="rounded border shadow-sm" width="50"
                                    height="50" style="object-fit: cover;">
                                <input type="file" name="profile_image" class="form-control rounded-3">
                            </div>
                        </div>
                    </div>

                    
                    <h5 class="text-primary fw-bold mb-3 border-bottom pb-2"><i
                            class="fas fa-graduation-cap me-2"></i>Academic Information</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Class</label>
                            <select name="class_id" id="class_id" class="form-select rounded-3" required>
                                <option value="">Select Class</option>
                                <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($class->id); ?>"
                                        <?php echo e($student->class_id == $class->id ? 'selected' : ''); ?>><?php echo e($class->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <div class="mt-1 small text-muted">
                                Class Teacher: <span id="teacher-name"
                                    class="fw-bold text-dark"><?php echo e($student->class && $student->class->teacher ? $student->class->teacher->name : 'N/A'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Section</label>
                            <select name="section_id" id="section_id" class="form-select rounded-3" required>
                                <option value="">Select Section</option>
                                <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($section->id); ?>"
                                        <?php echo e($student->section_id == $section->id ? 'selected' : ''); ?>><?php echo e($section->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Academic Year</label>
                            <select name="academic_year_id" class="form-select rounded-3" required>
                                <option value="">Select Year</option>
                                <?php $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($year->id); ?>"
                                        <?php echo e($student->academic_year_id == $year->id ? 'selected' : ''); ?>>
                                        <?php echo e($year->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>

                    
                    <h5 class="text-primary fw-bold mb-3 border-bottom pb-2"><i
                            class="fas fa-user-shield me-2"></i>Additional Details</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Security Role</label>
                            <select name="role_id" class="form-select rounded-3" required>
                                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($role->id); ?>"
                                        <?php echo e($student->role_id == $role->id ? 'selected' : ''); ?>><?php echo e(ucfirst($role->name)); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Parent</label>
                            <select name="parent_id" class="form-select rounded-3">
                                <option value="">Select Parent (Optional)</option>
                                <?php $__currentLoopData = $parents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($parent->id); ?>"
                                        <?php echo e($student->parent_id == $parent->id ? 'selected' : ''); ?>>
                                        <?php echo e($parent->parent_name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select rounded-3">
                                <option value="1" <?php echo e((int) $student->status === 1 ? 'selected' : ''); ?>>Active
                                </option>
                                <option value="0" <?php echo e((int) $student->status === 0 ? 'selected' : ''); ?>>Inactive
                                </option>
                            </select>
                        </div>
                    </div>

                    
                    <div class="d-flex justify-content-end gap-3 mt-4 border-top pt-3">
                        <a href="<?php echo e(route('students.index')); ?>"
                            class="btn btn-outline-secondary rounded-3 px-4">Discard</a>
                        <button type="submit" class="btn btn-primary rounded-3 px-5 shadow-sm">Update Profile</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        $(document).ready(function() {
            $('#class_id').on('change', function() {
                var classId = $(this).val();
                if (classId) {
                    // Update sections dynamically
                    $.get('/admin/get-sections/' + classId, function(data) {
                        $('#section_id').empty().append('<option value="">Select Section</option>');
                        $.each(data, function(_, value) {
                            $('#section_id').append('<option value="' + value.id + '">' +
                                value.name + '</option>');
                        });
                    });

                    // Update class teacher
                    $.get('/admin/get-class-details/' + classId, function(data) {
                        $('#teacher-name').text(data.teacher_name);
                    });
                }
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\pc\OneDrive\Desktop\school_lms\resources\views/admin/students/edit.blade.php ENDPATH**/ ?>
