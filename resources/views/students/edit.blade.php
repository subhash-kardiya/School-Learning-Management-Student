<?php $__env->startSection('title', 'Edit Student Profile'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid py-4 student-module-compact">

        
        <div class="mb-4 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
            <div>
                <a href="<?php echo e(route('students.index')); ?>" class="btn btn-link text-muted p-0 mb-2 text-decoration-none">
                    <i class="fas fa-arrow-left me-1"></i> Back to Student Directory
                </a>
            </div>
        </div>

        
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form action="<?php echo e(route('students.update', $student->id)); ?>" method="POST" enctype="multipart/form-data" id="studentEditForm">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            Please fix the highlighted fields and try again.
                        </div>
                    @endif

                    
                    <h5 class="text-primary fw-bold mb-3 border-bottom pb-2"><i class="fas fa-user me-2"></i>Personal
                        Details</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Student Name</label>
                            <input type="text" name="student_name" class="form-control rounded-3 @error('student_name') is-invalid @enderror"
                                value="<?php echo e(old('student_name', $student->student_name)); ?>">
                            @error('student_name')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Roll Number</label>
                            <input type="text" name="roll_no" class="form-control rounded-3 @error('roll_no') is-invalid @enderror"
                                value="<?php echo e(old('roll_no', $student->roll_no)); ?>">
                            @error('roll_no')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Username</label>
                            <input type="text" name="username" class="form-control rounded-3 @error('username') is-invalid @enderror"
                                value="<?php echo e(old('username', $student->username)); ?>">
                            @error('username')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control rounded-3 @error('email') is-invalid @enderror"
                                value="<?php echo e(old('email', $student->email)); ?>">
                            @error('email')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Password</label>
                            <input type="password" name="password" class="form-control rounded-3 @error('password') is-invalid @enderror"
                                placeholder="Leave blank to keep current">
                            @error('password')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Mobile Number</label>
                            <input type="text" name="mobile_no" class="form-control rounded-3 @error('mobile_no') is-invalid @enderror"
                                value="<?php echo e(old('mobile_no', $student->mobile_no)); ?>">
                            @error('mobile_no')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Gender</label>
                            <select name="gender" class="form-select rounded-3 @error('gender') is-invalid @enderror">
                                <option value="male" <?php echo e(old('gender', $student->gender) == 'male' ? 'selected' : ''); ?>>Male</option>
                                <option value="female" <?php echo e(old('gender', $student->gender) == 'female' ? 'selected' : ''); ?>>Female</option>
                                <option value="other" <?php echo e(old('gender', $student->gender) == 'other' ? 'selected' : ''); ?>>Other</option>
                            </select>
                            @error('gender')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control rounded-3 @error('date_of_birth') is-invalid @enderror"
                                value="<?php echo e(old('date_of_birth', $student->date_of_birth)); ?>">
                            @error('date_of_birth')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Address</label>
                            <input type="text" name="address" class="form-control rounded-3 @error('address') is-invalid @enderror"
                                value="<?php echo e(old('address', $student->address)); ?>">
                            @error('address')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">City</label>
                            <input type="text" name="city" class="form-control rounded-3 @error('city') is-invalid @enderror"
                                value="<?php echo e(old('city', $student->city)); ?>">
                            @error('city')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">State</label>
                            <input type="text" name="state" class="form-control rounded-3 @error('state') is-invalid @enderror"
                                value="<?php echo e(old('state', $student->state)); ?>">
                            @error('state')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Pincode</label>
                            <input type="text" name="pincode" class="form-control rounded-3 @error('pincode') is-invalid @enderror"
                                value="<?php echo e(old('pincode', $student->pincode)); ?>">
                            @error('pincode')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
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
                                <input type="file" name="profile_image" class="form-control rounded-3 @error('profile_image') is-invalid @enderror">
                            </div>
                            @error('profile_image')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    
                    <h5 class="text-primary fw-bold mb-3 border-bottom pb-2"><i
                            class="fas fa-graduation-cap me-2"></i>Academic Information</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Class</label>
                            <select name="class_id" id="class_id" class="form-select rounded-3 @error('class_id') is-invalid @enderror">
                                <option value="">Select Class</option>
                                <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($class->id); ?>"
                                        <?php echo e((int) old('class_id', $student->class_id) === (int) $class->id ? 'selected' : ''); ?>><?php echo e($class->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            @error('class_id')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                            <div class="mt-1 small text-muted">
                                Class Teacher: <span id="teacher-name"
                                    class="fw-bold text-dark"><?php echo e($student->class && $student->class->teacher ? $student->class->teacher->name : 'N/A'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Section</label>
                            <select name="section_id" id="section_id" class="form-select rounded-3 @error('section_id') is-invalid @enderror">
                                <option value="">Select Section</option>
                                <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($section->id); ?>"
                                        <?php echo e((int) old('section_id', $student->section_id) === (int) $section->id ? 'selected' : ''); ?>><?php echo e($section->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            @error('section_id')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Academic Year</label>
                            <select name="academic_year_id" class="form-select rounded-3 @error('academic_year_id') is-invalid @enderror">
                                <option value="">Select Year</option>
                                <?php $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($year->id); ?>"
                                        <?php echo e((int) old('academic_year_id', $student->academic_year_id) === (int) $year->id ? 'selected' : ''); ?>>
                                        <?php echo e($year->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            @error('academic_year_id')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    
                    <h5 class="text-primary fw-bold mb-3 border-bottom pb-2"><i
                            class="fas fa-user me-2"></i>Parent Details</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Parent Name</label>
                            <input type="text" name="parent_name" class="form-control rounded-3 @error('parent_name') is-invalid @enderror"
                                value="<?php echo e(old('parent_name', $student->parent->parent_name ?? '')); ?>">
                            @error('parent_name')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Parent Username</label>
                            <input type="text" name="parent_username" class="form-control rounded-3 @error('parent_username') is-invalid @enderror"
                                value="<?php echo e(old('parent_username', $student->parent->username ?? '')); ?>">
                            @error('parent_username')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Parent Email</label>
                            <input type="email" name="parent_email" class="form-control rounded-3 @error('parent_email') is-invalid @enderror"
                                value="<?php echo e(old('parent_email', $student->parent->email ?? '')); ?>">
                            @error('parent_email')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Parent Password</label>
                            <input type="password" name="parent_password"
                                class="form-control rounded-3 @error('parent_password') is-invalid @enderror"
                                placeholder="Leave blank to keep current">
                            @error('parent_password')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Parent Mobile No</label>
                            <input type="text" name="parent_mobile_no"
                                class="form-control rounded-3 @error('parent_mobile_no') is-invalid @enderror"
                                value="<?php echo e(old('parent_mobile_no', $student->parent->mobile_no ?? '')); ?>">
                            @error('parent_mobile_no')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select rounded-3 @error('status') is-invalid @enderror">
                                <option value="1" <?php echo e((string) old('status', (string) $student->status) === '1' ? 'selected' : ''); ?>>Active
                                </option>
                                <option value="0" <?php echo e((string) old('status', (string) $student->status) === '0' ? 'selected' : ''); ?>>Inactive
                                </option>
                            </select>
                            @error('status')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
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

<?php $__env->startPush('css'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/resize/student-compact.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        $(document).ready(function() {
            const oldSectionId = "<?php echo e(old('section_id')); ?>";

            function loadSections(classId) {
                if (!classId) {
                    $('#section_id').html('<option value="">Select Section</option>');
                    return;
                }

                $.get('/admin/get-sections/' + classId, function(data) {
                    $('#section_id').empty().append('<option value="">Select Section</option>');
                    $.each(data, function(_, value) {
                        const selected = oldSectionId && String(value.id) === String(oldSectionId) ? ' selected' : '';
                        $('#section_id').append('<option value="' + value.id + '"' + selected + '>' + value.name + '</option>');
                    });
                });

                $.get('/admin/get-class-details/' + classId, function(data) {
                    $('#teacher-name').text(data.teacher_name);
                });
            }

            $('#class_id').on('change', function() {
                loadSections($(this).val());
            });

            const initialClassId = $('#class_id').val();
            if (initialClassId && oldSectionId) {
                loadSections(initialClassId);
            }

            $('#studentEditForm').on('submit', function(e) {
                $('.client-error').remove();
                let hasError = false;

                const requiredFields = [
                    'student_name',
                    'roll_no',
                    'username',
                    'email',
                    'mobile_no',
                    'gender',
                    'date_of_birth',
                    'address',
                    'city',
                    'state',
                    'pincode',
                    'class_id',
                    'section_id',
                    'academic_year_id',
                    'parent_name',
                    'parent_username',
                    'parent_email',
                    'parent_mobile_no',
                    'status'
                ];

                requiredFields.forEach(function(name) {
                    const $field = $('[name="' + name + '"]');
                    if (!$field.length) return;

                    const isEmpty = String($field.val() || '').trim() === '';
                    if (isEmpty) {
                        hasError = true;
                        $field.addClass('is-invalid');
                        $field.after('<span class="text-danger d-block client-error">This field is required.</span>');
                    } else {
                        $field.removeClass('is-invalid');
                    }
                });

                if (hasError) {
                    e.preventDefault();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\pc\OneDrive\Desktop\school_lms\resources\views/admin/students/edit.blade.php ENDPATH**/ ?>
