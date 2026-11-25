<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GradePublication;
use App\Models\GradeRecord;
use App\Models\SchoolYear;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class GradePublicationController extends Controller
{


    /**
     * Display the Grade Publication page.
     */
    public function index(Request $request)
    {
        // Get all available school years
        $schoolYears = SchoolYear::orderByDesc('year_name')->get();

        // Get the currently active school year
        $activeSchoolYear = SchoolYear::active();

        // Determine the selected school year
        $requestedYear = $request->query('school_year');

        $selectedSchoolYear = $schoolYears->firstWhere(
            'year_name',
            $requestedYear
        );

        if (!$selectedSchoolYear) {
            $selectedSchoolYear = $activeSchoolYear
                ?? $schoolYears->first();
        }

        // Determine the selected term
        $requestedTerm = (int) $request->query('term');

        $selectedTerm = in_array($requestedTerm, [1, 2, 3], true)
            ? $requestedTerm
            : ($selectedSchoolYear?->current_term ?? 1);

        // Load publication history for the selected school year
        $publications = $selectedSchoolYear
            ? GradePublication::where(
                'school_year',
                $selectedSchoolYear->year_name
            )->with('publisher')->get()->keyBy('term')
            : collect();

        // Count approved grades per term
        $approvedCounts = $selectedSchoolYear
            ? GradeRecord::where(
                'school_year',
                $selectedSchoolYear->year_name
            )
                ->where('status', 'approved')
                ->selectRaw('term, COUNT(*) as total')
                ->groupBy('term')
                ->pluck('total', 'term')
            : collect();


        // Get complete publication history across all school years
        $publicationHistory = GradePublication::with('publisher')
            ->orderByDesc('published_at')
            ->get();

        return view('admin.grade-publications.index', compact(
            'schoolYears',
            'activeSchoolYear',
            'selectedSchoolYear',
            'selectedTerm',
            'publications',
            'approvedCounts',
            'publicationHistory'
        ));

    }



    /**
     * Publish approved grades for a selected school year and term.
     */
    public function publish(string $schoolYear, int $term)
    {
        // Validate term
        if (!in_array($term, [1, 2, 3], true)) {
            return back()->with('error', 'Invalid term selected.');
        }

        // Confirm that the school year exists
        $schoolYearRecord = SchoolYear::where('year_name', $schoolYear)
            ->first();

        if (!$schoolYearRecord) {
            return back()->with('error', 'The selected school year does not exist.');
        }

        // Check whether this term has already been published
        $existingPublication = GradePublication::where('school_year', $schoolYear)
            ->where('term', $term)
            ->first();

        if ($existingPublication) {
            return back()->with(
                'error',
                "Term {$term} for School Year {$schoolYear} has already been published."
            );
        }

        // Count approved grade records for this academic period
        $approvedCount = GradeRecord::where('school_year', $schoolYear)
            ->where('term', $term)
            ->where('status', 'approved')
            ->count();

        if ($approvedCount === 0) {
            return back()->with(
                'error',
                'There are no approved grades available for publication.'
            );
        }

        try {
            DB::transaction(function () use ($schoolYear, $term) {

                // Create one publication record for the entire school
                GradePublication::create([
                    'school_year' => $schoolYear,
                    'term' => $term,
                    'published_by' => Auth::id(),
                    'published_at' => now(),
                ]);

            });

        } catch (QueryException $exception) {

            // Protect against duplicate publication requests
            $existingPublication = GradePublication::where('school_year', $schoolYear)
                ->where('term', $term)
                ->first();

            if ($existingPublication) {
                return back()->with(
                    'error',
                    "Term {$term} for School Year {$schoolYear} has already been published."
                );
            }

            Log::error('Grade publication failed.', [
                'school_year' => $schoolYear,
                'term' => $term,
                'error' => $exception->getMessage(),
            ]);

            return back()->with(
                'error',
                'Unable to publish grades. Please try again.'
            );
        }

        return back()->with(
            'success',
            "Grades for Term {$term}, School Year {$schoolYear}, have been successfully published. {$approvedCount} approved grade records are now eligible for student viewing."
        );
    }
}
