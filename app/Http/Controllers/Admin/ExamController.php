<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Exam;
use App\Models\AcademicYear;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\Section;
use App\Models\TeacherMapping;
use App\Models\Student;



use Yajra\DataTables\Facades\DataTables;

class ExamController extends Controller
{
    // 📄 List page + DataTable
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Exam::with(['academicYear', 'class', 'section'])->latest();

            if (session('role') === 'teacher') {
                $teacherId = session('auth_id');
                $mappings = TeacherMapping::with(['section', 'subject'])->where('teacher_id', $teacherId)->get();

                if ($mappings->isEmpty()) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->where(function($q) use ($mappings) {
                        foreach ($mappings as $mapping) {
                            if ($mapping->section && $mapping->subject) {
                                $q->orWhere(function($subQuery) use ($mapping) {
                                    $subQuery->where('class_id', $mapping->section->class_id)
                                             ->where('subject_name', $mapping->subject->name)
                                             ->where(function($s) use ($mapping) {
                                                 $s->where('section_id', $mapping->section_id)
                                                   ->orWhereNull('section_id');
                                             });
                                });
                            }
                        }
                    });
                }
            }

            if ($request->filled('class_id')) {
                $query->where('class_id', $request->class_id);
            }

            if ($request->filled('section_id')) {
                $query->where('section_id', $request->section_id);
            }

            return DataTables::of($query)
                ->addColumn('class_section', function ($row) {
                    return ($row->class->name ?? '-') . ($row->section ? ' - ' . $row->section->name : '');
                })
                ->addColumn('start_date_display', function ($row) {
                    return $row->start_date ? $row->start_date->format('Y-m-d') : '-';
                })
                ->addColumn('end_date_display', function ($row) {
                    return $row->end_date ? $row->end_date->format('Y-m-d') : '-';
                })
                ->addColumn('time_display', function ($row) {
                    if (!$row->start_time) {
                        return '-';
                    }
                    $start = \Carbon\Carbon::parse($row->start_time)->format('h:i A');
                    $end = $row->end_time ? \Carbon\Carbon::parse($row->end_time)->format('h:i A') : '';
                    return trim($start . ' - ' . $end, ' -');
                })
                ->addColumn('status_badge', function ($row) {
                    return $row->status == 1
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>';
                })
                ->addColumn('action', function ($row) {
                    $showUrl = route('exams.show', $row->id);
                    $editUrl = route('exams.edit', $row->id);
                    $deleteUrl = route('exams.destroy', $row->id);
                    $csrf = csrf_token();

                    return '
                        <a href="' . $showUrl . '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                        <a href="' . $editUrl . '" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                        <form action="' . $deleteUrl . '" method="POST" class="d-inline" onsubmit="return confirm(\'Are you sure you want to delete this exam?\')">
                            <input type="hidden" name="_token" value="' . $csrf . '">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>';
                })
                ->addColumn('status', function ($row) {
                    return $row->status == 1
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-secondary">Inactive</span>';
                })
                ->addColumn('class', fn($row) => $row->class->name ?? '-')
                ->addColumn('year', fn($row) => $row->academicYear->name ?? '-')
                ->addColumn('subject', fn($row) => $row->subject_name ?? '-')
                ->rawColumns(['status', 'status_badge', 'action'])
                ->make(true);
        }

        $subjects = Subject::where('status', 1)->get();
        // Note: $exams variable was passed in original code but not defined in non-ajax block. 
        // Removing it from compact to prevent undefined variable error, or keeping if it was intended to be empty.
        $exams = []; 
        return view('exams.index', compact('exams', 'subjects'));
    }

    // ➕ Create form
  public function create()
{
    $academicYears = AcademicYear::all();
    
    if (session('role') === 'teacher') {
        $teacherId = session('auth_id');
        $mappings = TeacherMapping::with(['section.class', 'subject'])->where('teacher_id', $teacherId)->get();
        
        $classIds = $mappings->pluck('section.class_id')->unique();
        $allClassIds = $classIds->unique();
        
        $classes = Classes::whereIn('id', $allClassIds)->where('status', 1)->get();
        $sectionIds = $mappings->pluck('section_id')->unique();
        $sections = Section::with('class')->whereIn('id', $sectionIds)->where('status', 1)->get();
        
        $subjectIds = $mappings->pluck('subject_id')->unique();
        $subjects = Subject::with('class')->whereIn('id', $subjectIds)->where('status', 1)->get();

        $sectionSubjects = $mappings->map(function ($mapping) {
            return [
                'section_id' => $mapping->section_id,
                'class_id' => $mapping->section->class_id ?? null,
                'subject_name' => $mapping->subject->name ?? null,
            ];
        })->values();

        $examsQuery = Exam::with(['academicYear', 'class', 'section'])->latest();

        if ($mappings->isEmpty()) {
            $exams = collect();
        } else {
            $examsQuery->where(function($q) use ($mappings) {
                foreach ($mappings as $mapping) {
                    if ($mapping->section && $mapping->subject) {
                        $q->orWhere(function($subQuery) use ($mapping) {
                            $subQuery->where('class_id', $mapping->section->class_id)
                                     ->where('subject_name', $mapping->subject->name)
                                     ->where(function($s) use ($mapping) {
                                         $s->where('section_id', $mapping->section_id)
                                           ->orWhereNull('section_id');
                                     });
                        });
                    }
                }
            });
            $exams = $examsQuery->get();
        }
    } else {
        $classes = Classes::where('status', 1)->get();
        $sections = Section::with('class')->where('status', 1)->get();
        $exams = Exam::with(['academicYear', 'class', 'section'])->latest()->get();
        $subjects = Subject::with('class')->where('status', 1)->get();

        $sectionSubjects = TeacherMapping::with(['section:id,class_id', 'subject:id,name,class_id'])
            ->get()
            ->map(function ($mapping) {
                return [
                    'section_id' => $mapping->section_id,
                    'class_id' => $mapping->section->class_id ?? null,
                    'subject_name' => $mapping->subject->name ?? null,
                ];
            })
            ->filter(function ($item) {
                return !empty($item['section_id']) && !empty($item['class_id']) && !empty($item['subject_name']);
            })
            ->values();
    }

    return view('exams.createexam', compact('academicYears', 'classes', 'sections', 'exams', 'subjects', 'sectionSubjects'));
}



    // 💾 Store
    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('Exam Store Request:', $request->all());
        
        try {
            $data = $request->validate([
                'name'             => 'required|string|max:255',
                'academic_year_id' => 'required|exists:academic_years,id',
                'class_id'         => 'required|exists:classes,id',
                'section_id'       => 'nullable|exists:sections,id',
                'subject_name'     => 'required|string',
                'start_date'       => 'required|date',
                'end_date'         => 'required|date|after_or_equal:start_date',
                'start_time'       => 'required|date_format:H:i',
                'end_time'         => 'required|date_format:H:i|after:start_time',
                'room_no'          => 'required|string|max:255',
                'total_mark'       => 'required|integer|min:0',
                'passing_mark'     => 'required|integer|min:0',
                'status'           => 'required|boolean',
            ]);

            $blockedDateMessage = $this->findBlockedDateMessage($data);
            if ($blockedDateMessage) {
                return $this->buildConflictResponse($request, $blockedDateMessage);
            }

            $conflictMessage = $this->findExamConflictMessage($data, null, false);
            if ($conflictMessage) {
                return $this->buildConflictResponse($request, $conflictMessage);
            }

            $exam = Exam::create($data);
            
            \Illuminate\Support\Facades\Log::info('Exam Created:', $exam->toArray());

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Exam created successfully!',
                    'exam' => $exam
                ]);
            }

            return redirect()->route('exams.createexam')->with('success', 'Exam created successfully!');
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Exam Store Error: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Server Error: ' . $e->getMessage()
                ], 500);
            }
            throw $e;
        }
    }

    public function edit($id)
    {
        $exam = Exam::findOrFail($id);
        $academicYears = AcademicYear::all();
        
        if (session('role') === 'teacher') {
            $teacherId = session('auth_id');
            $mappings = TeacherMapping::with(['section.class', 'subject'])->where('teacher_id', $teacherId)->get();
            
            $classIds = $mappings->pluck('section.class_id')->unique();
            $allClassIds = $classIds->unique();
            
            $classes = Classes::whereIn('id', $allClassIds)->where('status', 1)->get();
            $sectionIds = $mappings->pluck('section_id')->unique();
            $sections = Section::with('class')->whereIn('id', $sectionIds)->where('status', 1)->get();
            
            $subjectIds = $mappings->pluck('subject_id')->unique();
            $subjects = Subject::with('class')->whereIn('id', $subjectIds)->where('status', 1)->get();

            $sectionSubjects = $mappings->map(function ($mapping) {
                return [
                    'section_id' => $mapping->section_id,
                    'class_id' => $mapping->section->class_id ?? null,
                    'subject_name' => $mapping->subject->name ?? null,
                ];
            })->values();
        } else {
            $classes = Classes::where('status', 1)->get();
            $sections = Section::with('class')->where('status', 1)->get();
            $subjects = Subject::with('class')->where('status', 1)->get();

            $sectionSubjects = TeacherMapping::with(['section:id,class_id', 'subject:id,name,class_id'])
                ->get()
                ->map(function ($mapping) {
                    return [
                        'section_id' => $mapping->section_id,
                        'class_id' => $mapping->section->class_id ?? null,
                        'subject_name' => $mapping->subject->name ?? null,
                    ];
                })
                ->filter(function ($item) {
                    return !empty($item['section_id']) && !empty($item['class_id']) && !empty($item['subject_name']);
                })
                ->values();
        }

        return view('exams.edit', compact('exam', 'academicYears', 'classes', 'sections','subjects', 'sectionSubjects'));
    }

    public function update(Request $request, $id)
    {
        $exam = Exam::findOrFail($id);

        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_id'         => 'required|exists:classes,id',
            'section_id'       => 'nullable|exists:sections,id',
            'subject_name'     => 'required|string',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after_or_equal:start_date',
            'start_time'       => 'required|date_format:H:i',
            'end_time'         => 'required|date_format:H:i|after:start_time',
                'room_no'          => 'required|string|max:255',
            'total_mark'       => 'required|integer|min:0',
            'passing_mark'     => 'required|integer|min:0',
            'status'           => 'required|boolean',
        ]);

        $existingStartDate = $exam->start_date ? $exam->start_date->format('Y-m-d') : null;
        $existingEndDate = $exam->end_date ? $exam->end_date->format('Y-m-d') : null;
        $isDateChanged = ((string) $existingStartDate !== (string) $data['start_date'])
            || ((string) $existingEndDate !== (string) $data['end_date']);

        // Only enforce Sunday/holiday/festival date restriction when date is changed.
        if ($isDateChanged) {
            $blockedDateMessage = $this->findBlockedDateMessage($data);
            if ($blockedDateMessage) {
                return $this->buildConflictResponse($request, $blockedDateMessage);
            }
        }

        $conflictMessage = $this->findExamConflictMessage($data, (int) $id, false);
        if ($conflictMessage) {
            return $this->buildConflictResponse($request, $conflictMessage);
        }

        $exam->update($data);

        return redirect()->route('exams.createexam')->with('success', 'Exam updated successfully.');
    }

    public function schedule(Request $request)
    {
        $selectedAcademicYear = $request->query('academic_year_id');
        $selectedClass = $request->query('class_id');
        $selectedSection = $request->query('section_id');

        $examsQuery = Exam::with(['academicYear', 'class', 'section'])->latest();
        $classesQuery = Classes::where('status', 1);
        $sectionsQuery = Section::with('class')->where('status', 1);

        $role = session('role');
        $authId = session('auth_id');

        if ($role === 'teacher') {
            $teacherId = $authId;
            $mappings = TeacherMapping::with(['section', 'subject'])->where('teacher_id', $teacherId)->get();

            if ($mappings->isEmpty()) {
                $examsQuery->whereRaw('1 = 0');
                $classesQuery->whereRaw('1 = 0');
                $sectionsQuery->whereRaw('1 = 0');
            } else {
                $examsQuery->where(function ($q) use ($mappings) {
                    foreach ($mappings as $mapping) {
                        if ($mapping->section && $mapping->subject) {
                            $q->orWhere(function ($subQuery) use ($mapping) {
                                $subQuery->where('class_id', $mapping->section->class_id)
                                    ->where('subject_name', $mapping->subject->name)
                                    ->where(function ($s) use ($mapping) {
                                        $s->where('section_id', $mapping->section_id)
                                            ->orWhereNull('section_id');
                                    });
                            });
                        }
                    }
                });

                $allowedClassIds = $mappings->pluck('section.class_id')
                    ->filter()
                    ->unique()
                    ->values();
                $allowedSectionIds = $mappings->pluck('section_id')
                    ->filter()
                    ->unique()
                    ->values();

                if ($allowedClassIds->isEmpty()) {
                    $classesQuery->whereRaw('1 = 0');
                } else {
                    $classesQuery->whereIn('id', $allowedClassIds);
                }

                if ($allowedSectionIds->isEmpty()) {
                    $sectionsQuery->whereRaw('1 = 0');
                } else {
                    $sectionsQuery->whereIn('id', $allowedSectionIds);
                }
            }
        } elseif ($role === 'student') {
            $student = Student::find($authId);

            if (!$student) {
                $examsQuery->whereRaw('1 = 0');
                $classesQuery->whereRaw('1 = 0');
                $sectionsQuery->whereRaw('1 = 0');
            } else {
                // Force student context so manual/stale query params don't hide valid exams.
                $selectedAcademicYear = $student->academic_year_id;
                $selectedClass = $student->class_id;

                $isSectionInClass = !empty($student->section_id) && Section::where('id', $student->section_id)
                    ->where('class_id', $student->class_id)
                    ->exists();

                $selectedSection = $isSectionInClass ? $student->section_id : null;

                $examsQuery->where('class_id', $student->class_id);
                if ($isSectionInClass) {
                    $examsQuery->where(function ($q) use ($student) {
                        $q->where('section_id', $student->section_id)
                            ->orWhereNull('section_id');
                    });
                }

                if (!empty($student->academic_year_id)) {
                    $examsQuery->where('academic_year_id', $student->academic_year_id);
                }

                $classesQuery->where('id', $student->class_id);
                if ($isSectionInClass) {
                    $sectionsQuery->where('id', $student->section_id);
                } else {
                    $sectionsQuery->where('class_id', $student->class_id);
                }
            }
        }

        if (!empty($selectedAcademicYear)) {
            $examsQuery->where('academic_year_id', $selectedAcademicYear);
        }

        if (!empty($selectedClass)) {
            $examsQuery->where('class_id', $selectedClass);
        }

        if (!empty($selectedSection)) {
            $examsQuery->where('section_id', $selectedSection);
        }

        $exams = $examsQuery->get();
        $academicYears = AcademicYear::all();

        if (!empty($selectedAcademicYear)) {
            $classesQuery->where('academic_year_id', $selectedAcademicYear);
        }
        $classes = $classesQuery->get();

        if (!empty($selectedClass)) {
            $sectionsQuery->where('class_id', $selectedClass);
        } elseif (!empty($selectedAcademicYear)) {
            $sectionsQuery->whereHas('class', function ($q) use ($selectedAcademicYear) {
                $q->where('academic_year_id', $selectedAcademicYear);
            });
        }
        $sections = $sectionsQuery->get();

        if (!empty($selectedSection) && !$sections->contains('id', (int) $selectedSection)) {
            $selectedSection = null;
        }

        return view('exams.schedule', compact(
            'exams',
            'academicYears',
            'classes',
            'sections',
            'selectedAcademicYear',
            'selectedClass',
            'selectedSection'
        ));
    }
    public function show($id)
    {
        $exam = Exam::with(['academicYear', 'class', 'section'])->findOrFail($id);
        return view('exams.show', compact('exam'));
    }

    public function destroy(Request $request, $id)
    {
        Exam::findOrFail($id)->delete();
        if (!$request->ajax()) {
            return redirect()->back()->with('success', 'Exam deleted successfully.');
        }
        return response()->json(['success' => true]);
    }

    // 🔍 Get Exams by Context (for Create Panel list)
    public function getExamsByContext(Request $request) 
    {
        // ... existing code ...
        $exams = Exam::where('academic_year_id', $request->academic_year_id)
            ->where('class_id', $request->class_id)
            ->latest()
            ->get();

        return response()->json($exams);
    }

    // 📦 Batch Store
    public function batchStore(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('Batch Store Request:', $request->all());

        $data = $request->validate([
            'exams' => 'required|array',
            'exams.*.name' => 'required|string',
            'exams.*.start_date' => 'required|date',
            'exams.*.end_date' => 'required|date|after_or_equal:exams.*.start_date',
            'exams.*.start_time' => 'required|date_format:H:i',
            'exams.*.end_time' => 'required|date_format:H:i|after:exams.*.start_time',
            'exams.*.room_no' => 'required|string|max:255',
            'exams.*.section_id' => 'nullable|exists:sections,id',
            'exams.*.subject_name' => 'required|string',
            'exams.*.total_mark' => 'required|integer',
            'exams.*.passing_mark' => 'required|integer',
            'exams.*.academic_year_id' => 'required|exists:academic_years,id',
            'exams.*.class_id' => 'required|exists:classes,id',
            'exams.*.status' => 'required',
        ]);

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            foreach ($data['exams'] as $examData) {
                $blockedDateMessage = $this->findBlockedDateMessage($examData);
                if ($blockedDateMessage) {
                    \Illuminate\Support\Facades\DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => $blockedDateMessage
                    ], 422);
                }

                $conflictMessage = $this->findExamConflictMessage($examData, null, false);
                if ($conflictMessage) {
                    \Illuminate\Support\Facades\DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => $conflictMessage
                    ], 422);
                }

                Exam::create($examData);
            }

            \Illuminate\Support\Facades\DB::commit();

            return response()->json(['success' => true, 'message' => 'Exams created successfully!']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Batch Store Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function declareResult(Request $request, $id)
    {
        $exam = Exam::findOrFail($id);
        $exam->update(['result_declared' => 1]);
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Result declared successfully.']);
        }
        return redirect()->back()->with('success', 'Result declared successfully.');
    }

    public function undeclareResult(Request $request, $id)
    {
        $exam = Exam::findOrFail($id);
        $exam->update(['result_declared' => 0]);
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Result unlocked successfully.']);
        }
        return redirect()->back()->with('success', 'Result unlocked successfully.');
    }

    private function buildConflictResponse(Request $request, string $message)
    {
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        return redirect()->back()
            ->withInput()
            ->with('error', $message)
            ->withErrors([
                'exam_conflict' => $message,
            ]);
    }

    private function findExamConflictMessage(array $data, ?int $ignoreExamId = null, bool $checkClassSectionSlot = true): ?string
    {
        $sectionId = !empty($data['section_id']) ? (int) $data['section_id'] : null;
        $subjectName = strtolower(trim((string) ($data['subject_name'] ?? '')));
        $roomNo = trim((string) ($data['room_no'] ?? ''));
        $newStartDate = (string) $data['start_date'];
        $newEndDate = (string) $data['end_date'];
        $newStartTime = !empty($data['start_time']) ? (string) $data['start_time'] : null;
        $newEndTime = !empty($data['end_time']) ? (string) $data['end_time'] : null;

        $applyDateOverlap = function ($query) use ($newStartDate, $newEndDate) {
            $query->whereDate('start_date', '<=', $newEndDate)
                ->whereDate('end_date', '>=', $newStartDate);
        };

        $applyTimeOverlap = function ($query) use ($newStartTime, $newEndTime) {
            if (empty($newStartTime) || empty($newEndTime)) {
                // If time is missing on new exam, treat as full-day block for overlapping dates.
                return;
            }

            $query->where(function ($q) use ($newStartTime, $newEndTime) {
                $q->whereNull('start_time')
                    ->orWhereNull('end_time')
                    ->orWhere(function ($timeQuery) use ($newStartTime, $newEndTime) {
                        $timeQuery->whereTime('start_time', '<', $newEndTime)
                            ->whereTime('end_time', '>', $newStartTime);
                    });
            });
        };

        $baseQuery = function () use ($ignoreExamId) {
            return Exam::query()
                ->when($ignoreExamId, function ($q) use ($ignoreExamId) {
                    $q->where('id', '!=', $ignoreExamId);
                });
        };

        // Exact/near duplicate in same academic context.
        $duplicateQuery = $baseQuery()
            ->where('academic_year_id', $data['academic_year_id'])
            ->where('class_id', $data['class_id'])
            ->where(function ($q) use ($sectionId) {
                if ($sectionId) {
                    $q->where('section_id', $sectionId);
                } else {
                    $q->whereNull('section_id');
                }
            })
            ->whereRaw('LOWER(TRIM(subject_name)) = ?', [$subjectName]);
        $applyDateOverlap($duplicateQuery);
        $applyTimeOverlap($duplicateQuery);
        if ($duplicateQuery->exists()) {
            return 'This exam is already scheduled for the same class, section, subject, date and time.';
        }

        // Room availability check.
        $roomQuery = $baseQuery()->where('room_no', $roomNo);
        $applyDateOverlap($roomQuery);
        $applyTimeOverlap($roomQuery);
        if ($roomQuery->exists()) {
            return 'Room is not available for the selected date/time. Please choose another room or slot.';
        }

        // Class/Section slot clash check.
        if ($checkClassSectionSlot) {
            $classSectionQuery = $baseQuery()
                ->where('academic_year_id', $data['academic_year_id'])
                ->where('class_id', $data['class_id'])
                ->where(function ($q) use ($sectionId) {
                    if ($sectionId) {
                        $q->where('section_id', $sectionId);
                    } else {
                        $q->whereNull('section_id');
                    }
                });
            $applyDateOverlap($classSectionQuery);
            $applyTimeOverlap($classSectionQuery);
            if ($classSectionQuery->exists()) {
                return 'Another exam is already scheduled for this class/section in the selected date/time.';
            }
        }

        return null;
    }

    private function findBlockedDateMessage(array $data): ?string
    {
        $startDate = \Carbon\Carbon::parse((string) $data['start_date'])->startOfDay();
        $endDate = \Carbon\Carbon::parse((string) $data['end_date'])->startOfDay();

        $holidayDate = $this->findBlockedDateFromTable('holidays', $startDate, $endDate);
        if ($holidayDate) {
            return 'Exam date falls on a holiday (' . $holidayDate . '). Please choose another date.';
        }

        $festivalDate = $this->findBlockedDateFromTable('festivals', $startDate, $endDate);
        if ($festivalDate) {
            return 'Exam date falls on a festival (' . $festivalDate . '). Please choose another date.';
        }

        return null;
    }

    private function findBlockedDateFromTable(string $table, \Carbon\Carbon $startDate, \Carbon\Carbon $endDate): ?string
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable($table)) {
            return null;
        }

        $columns = \Illuminate\Support\Facades\Schema::getColumnListing($table);

        $singleDateColumn = collect(['date', 'event_date', 'holiday_date', 'festival_date'])
            ->first(fn($col) => in_array($col, $columns, true));

        if ($singleDateColumn) {
            $row = \Illuminate\Support\Facades\DB::table($table)
                ->whereDate($singleDateColumn, '>=', $startDate->toDateString())
                ->whereDate($singleDateColumn, '<=', $endDate->toDateString())
                ->orderBy($singleDateColumn)
                ->first([$singleDateColumn]);

            if ($row && !empty($row->{$singleDateColumn})) {
                return (string) $row->{$singleDateColumn};
            }
        }

        $rangeStartColumn = collect(['start_date', 'from_date', 'date_from'])
            ->first(fn($col) => in_array($col, $columns, true));
        $rangeEndColumn = collect(['end_date', 'to_date', 'date_to'])
            ->first(fn($col) => in_array($col, $columns, true));

        if ($rangeStartColumn && $rangeEndColumn) {
            $row = \Illuminate\Support\Facades\DB::table($table)
                ->whereDate($rangeStartColumn, '<=', $endDate->toDateString())
                ->whereDate($rangeEndColumn, '>=', $startDate->toDateString())
                ->orderBy($rangeStartColumn)
                ->first([$rangeStartColumn, $rangeEndColumn]);

            if ($row && !empty($row->{$rangeStartColumn}) && !empty($row->{$rangeEndColumn})) {
                $blockedStart = \Carbon\Carbon::parse((string) $row->{$rangeStartColumn})->startOfDay();
                if ($blockedStart->greaterThanOrEqualTo($startDate) && $blockedStart->lessThanOrEqualTo($endDate)) {
                    return $blockedStart->toDateString();
                }

                return $startDate->toDateString();
            }
        }

        return null;
    }
}
