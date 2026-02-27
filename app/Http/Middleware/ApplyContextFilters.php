<?php

namespace App\Http\Middleware;

use App\Models\AcademicYear;
use App\Models\Classes;
use App\Models\Section;
use Closure;
use Illuminate\Http\Request;

class ApplyContextFilters
{
    public function handle(Request $request, Closure $next)
    {
        if (session()->has('auth_id')) {
            // Always sync context to the currently active academic year.
            $activeYear = AcademicYear::where('is_active', 1)->first();
            if ($activeYear) {
                $selectedYearId = (int) session('selected_academic_year_id');
                if ($selectedYearId !== (int) $activeYear->id) {
                    session([
                        'selected_academic_year_id' => (int) $activeYear->id,
                        'selected_class_id' => null,
                        'selected_section_id' => null,
                    ]);
                }
            }

            // REDIRECT TO SETTINGS IF NO YEAR SELECTED (AND NOT ON SETTINGS PAGE)
            if (!session()->has('selected_academic_year_id') &&
                !$request->routeIs('settings.index') &&
                !$request->routeIs('academic.year.*') &&
                !$request->is('logout') &&
                !$request->ajax()) {

                // Allow admins to access academic year management even if no year is selected
                // (Otherwise they can't create the first academic year)
                return redirect()->route('settings.index')->with('error', 'Please select or create an academic year to continue.');
            }

            if ($request->isMethod('GET')) {
                $merge = [];
                if (!$request->filled('academic_year_id') && session()->has('selected_academic_year_id')) {
                    $merge['academic_year_id'] = session('selected_academic_year_id');
                }
                if (!$request->filled('class_id') && session()->has('selected_class_id')) {
                    $merge['class_id'] = session('selected_class_id');
                }
                if (!$request->filled('section_id') && session()->has('selected_section_id')) {
                    $merge['section_id'] = session('selected_section_id');
                }
                if (!empty($merge)) {
                    $request->merge($merge);
                }
            }

            $selectedYearId = session('selected_academic_year_id');
            $selectedClassId = session('selected_class_id');
            $selectedSectionId = session('selected_section_id');

            $classes = Classes::query()
                ->when($selectedYearId, fn($q) => $q->where('academic_year_id', $selectedYearId))
                ->orderBy('name')
                ->get();

            if ($selectedClassId && !$classes->pluck('id')->contains((int) $selectedClassId)) {
                session()->forget(['selected_class_id', 'selected_section_id']);
                $selectedClassId = null;
                $selectedSectionId = null;
            }

            $sections = collect();
            if ($selectedClassId) {
                $sections = Section::where('class_id', $selectedClassId)->orderBy('name')->get();
                if ($selectedSectionId && !$sections->pluck('id')->contains((int) $selectedSectionId)) {
                    session()->forget('selected_section_id');
                    $selectedSectionId = null;
                }
            }

            view()->share('globalAcademicYears', AcademicYear::where('is_active', 1)->orderBy('name')->get());
            view()->share('globalClasses', $classes);
            view()->share('globalSections', $sections);
            view()->share('selectedAcademicYearId', (int) $selectedYearId);
            view()->share('selectedClassId', (int) $selectedClassId);
            view()->share('selectedSectionId', (int) $selectedSectionId);
        }

        return $next($request);
    }
}
