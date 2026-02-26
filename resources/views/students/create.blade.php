<?php $__env->startSection('title', 'Student Admission'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4 student-module-compact">


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

            @if ($errors->any())
                <div class="alert alert-danger">
                    Please fix the highlighted fields and try again.
                </div>
            @endif


            <form action="<?php echo e(route('students.store')); ?>" method="POST" enctype="multipart/form-data" id="studentCreateForm">
                <?php echo csrf_field(); ?>


                <h6 class="fw-bold text-primary border-bottom pb-2 mb-4">Personal Details</h6>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Student Name</label>
                        <input type="text" name="student_name" class="form-control @error('student_name') is-invalid @enderror"
                            value="<?php echo e(old('student_name')); ?>">
                        @error('student_name')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Roll Number</label>
                        <input type="text" name="roll_no" class="form-control @error('roll_no') is-invalid @enderror"
                            value="<?php echo e(old('roll_no')); ?>">
                        @error('roll_no')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                            value="<?php echo e(old('username')); ?>">
                        @error('username')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            value="<?php echo e(old('email')); ?>">
                        @error('email')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                        @error('password')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Mobile No</label>
                        <input type="text" name="mobile_no" class="form-control @error('mobile_no') is-invalid @enderror"
                            value="<?php echo e(old('mobile_no')); ?>">
                        @error('mobile_no')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                            <option value="">Select</option>
                            <option value="male" <?php echo e(old('gender') == 'male' ? 'selected' : ''); ?>>Male</option>
                            <option value="female" <?php echo e(old('gender') == 'female' ? 'selected' : ''); ?>>Female</option>
                            <option value="other" <?php echo e(old('gender') == 'other' ? 'selected' : ''); ?>>Other</option>
                        </select>
                        @error('gender')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror"
                            value="<?php echo e(old('date_of_birth')); ?>">
                        @error('date_of_birth')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2"><?php echo e(old('address')); ?></textarea>
                        @error('address')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control @error('city') is-invalid @enderror"
                            value="<?php echo e(old('city')); ?>">
                        @error('city')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">State</label>
                        <input type="text" name="state" class="form-control @error('state') is-invalid @enderror"
                            value="<?php echo e(old('state')); ?>">
                        @error('state')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Pincode</label>
                        <input type="text" name="pincode" class="form-control @error('pincode') is-invalid @enderror"
                            value="<?php echo e(old('pincode')); ?>">
                        @error('pincode')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>


                <h6 class="fw-bold text-primary border-bottom pb-2 mt-5 mb-4">Academic Details</h6>

                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Class</label>
                        <select name="class_id" id="class_id" class="form-select @error('class_id') is-invalid @enderror">
                            <option value="">Select Class</option>
                            <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($class->id); ?>" <?php echo e((int) old('class_id') === (int) $class->id ? 'selected' : ''); ?>><?php echo e($class->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        @error('class_id')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Section</label>
                        <select name="section_id" id="section_id" class="form-select @error('section_id') is-invalid @enderror" disabled>
                            <option value="">Select Section</option>
                        </select>
                        @error('section_id')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Academic Year</label>
                        <select name="academic_year_id" class="form-select @error('academic_year_id') is-invalid @enderror">
                            @foreach ($globalAcademicYears ?? collect() as $year)
                                <option value="{{ $year->id }}"
                                    {{ (int) old('academic_year_id', $selectedAcademicYearId ?? 0) === (int) $year->id ? 'selected' : '' }}>
                                    {{ $year->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('academic_year_id')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>

                </div>

                <h6 class="fw-bold text-primary border-bottom pb-2 mt-5 mb-4">Parent Details</h6>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Parent Name</label>
                        <input type="text" name="parent_name" class="form-control @error('parent_name') is-invalid @enderror"
                            value="<?php echo e(old('parent_name')); ?>">
                        @error('parent_name')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Parent Username</label>
                        <input type="text" name="parent_username" class="form-control @error('parent_username') is-invalid @enderror"
                            value="<?php echo e(old('parent_username')); ?>">
                        @error('parent_username')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Parent Email</label>
                        <input type="email" name="parent_email" class="form-control @error('parent_email') is-invalid @enderror"
                            value="<?php echo e(old('parent_email')); ?>">
                        @error('parent_email')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Parent Password</label>
                        <input type="password" name="parent_password" class="form-control @error('parent_password') is-invalid @enderror">
                        @error('parent_password')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Parent Mobile No</label>
                        <input type="text" name="parent_mobile_no" class="form-control @error('parent_mobile_no') is-invalid @enderror"
                            value="<?php echo e(old('parent_mobile_no')); ?>">
                        @error('parent_mobile_no')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <h6 class="fw-bold text-primary border-bottom pb-2 mt-5 mb-4">System Details</h6>

                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="1" <?php echo e((string) old('status', '1') === '1' ? 'selected' : ''); ?>>Active</option>
                            <option value="0" <?php echo e((string) old('status') === '0' ? 'selected' : ''); ?>>Inactive</option>
                        </select>
                        @error('status')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Profile Image</label>
                        <input type="file" name="profile_image" class="form-control @error('profile_image') is-invalid @enderror">
                        @error('profile_image')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
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

<?php $__env->startPush('css'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/resize/student-compact.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    (function() {
        const oldSectionId = "<?php echo e(old('section_id')); ?>";

        function loadSections(classId) {
            if (!classId) {
                $('#section_id').prop('disabled', true).html('<option value=\"\">Select Section</option>');
                return;
            }

            $.get('/admin/get-sections/' + classId, function(data) {
                $('#section_id').prop('disabled', false).empty().append('<option value="">Select Section</option>');

                data.forEach(section => {
                    const selected = oldSectionId && String(section.id) === String(oldSectionId) ? ' selected' : '';
                    $('#section_id').append(
                        `<option value="${section.id}"${selected}>${section.name}</option>`
                    );
                });
            });
        }
        $('#class_id').on('change', function() {
            loadSections($(this).val());
        });

        const initialClassId = $('#class_id').val();
        if (initialClassId) {
            loadSections(initialClassId);
        }

        // Client-side required validation to prevent unwanted redirect and show missing fields immediately.
        $('#studentCreateForm').on('submit', function(e) {
            $('.client-error').remove();
            let hasError = false;

                const requiredFields = [
                'student_name',
                'roll_no',
                'username',
                'email',
                'password',
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
                'parent_password',
                'parent_mobile_no',
                'status',
                'profile_image'
            ];

            requiredFields.forEach(function(name) {
                const $field = $('[name="' + name + '"]');
                if (!$field.length) {
                    return;
                }

                const type = ($field.attr('type') || '').toLowerCase();
                const isEmpty = type === 'file'
                    ? (!$field[0].files || !$field[0].files.length)
                    : String($field.val() || '').trim() === '';

                if (isEmpty) {
                    hasError = true;
                    $field.addClass('is-invalid');
                    $field.after('<span class="text-danger d-block client-error">This field is required.</span>');
                } else if ($field.hasClass('is-invalid')) {
                    $field.removeClass('is-invalid');
                }
            });

            if (hasError) {
                e.preventDefault();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    })();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\pc\OneDrive\Desktop\school_lms\resources\views/admin/students/create.blade.php ENDPATH**/ ?>
