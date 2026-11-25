@extends('layouts.admin')

@section('title', 'Grade Publication')

@section('header', 'Grade Publication')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/admin/grade-publications.css') }}">
@endsection

@section('content')

    <div class="grade-publications-page">

       

        {{-- FLASH MESSAGES --}}
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        {{-- ACTIVE SCHOOL YEAR INFORMATION --}}
        <div class="publication-info-card">

            <div class="publication-info-icon">
                <i class="fas fa-calendar-check"></i>
            </div>

            <div>
                <span>Currently Active School Year</span>

                <h3>
                    {{ $activeSchoolYear?->year_name ?? 'No Active School Year' }}
                </h3>

                @if($activeSchoolYear)
                    <p>
                        Active Term:
                        <strong>{{ $activeSchoolYear->current_term_label }}</strong>
                    </p>
                @endif
            </div>

        </div>

        {{-- SCHOOL YEAR AND TERM FILTER --}}
        <div class="publication-filter-card">

            <form method="GET" action="{{ route('admin.grade-publications.index') }}" class="publication-filter-form">

                <div class="filter-group">
                    <label for="school_year">School Year</label>

                    <select name="school_year" id="school_year">
                        @foreach($schoolYears as $year)
                            <option value="{{ $year->year_name }}" {{ $selectedSchoolYear?->id === $year->id ? 'selected' : '' }}>
                                {{ $year->year_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label for="term">Term</label>

                    <select name="term" id="term">
                        <option value="1" {{ $selectedTerm === 1 ? 'selected' : '' }}>
                            Term 1
                        </option>

                        <option value="2" {{ $selectedTerm === 2 ? 'selected' : '' }}>
                            Term 2
                        </option>

                        <option value="3" {{ $selectedTerm === 3 ? 'selected' : '' }}>
                            Term 3
                        </option>
                    </select>
                </div>

                <button type="submit" class="filter-button">
                    <i class="fas fa-filter"></i>
                    Apply Filter
                </button>

            </form>

        </div>

        @if($selectedSchoolYear)

            @php
                $publication = $publications->get($selectedTerm);
                $approvedCount = $approvedCounts->get($selectedTerm, 0);
            @endphp

            {{-- PUBLICATION STATUS --}}
            <div class="publication-status-card">

                <div class="publication-status-header">

                    <div>
                        <span class="section-label">Selected Academic Period</span>

                        <h2>
                            {{ $selectedSchoolYear->year_name }}
                            - Term {{ $selectedTerm }}
                        </h2>
                    </div>

                    @if($publication)
                        <span class="publication-badge published">
                            <i class="fas fa-check-circle"></i>
                            Published
                        </span>
                    @else
                        <span class="publication-badge unpublished">
                            <i class="fas fa-clock"></i>
                            Not Published
                        </span>
                    @endif

                </div>

                {{-- PUBLICATION DETAILS --}}
                <div class="publication-details">

                    <div class="publication-detail-item">
                        <span>Approved Grade Records</span>
                        <strong>{{ number_format($approvedCount) }}</strong>
                    </div>

                    <div class="publication-detail-item">
                        <span>Publication Date</span>

                        <strong>
                            {{ $publication?->published_at?->format('M d, Y h:i A') ?? '—' }}
                        </strong>
                    </div>

                    <div class="publication-detail-item">
                        <span>Published By</span>

                        <strong>
                            {{ $publication?->publisher?->name ?? '—' }}
                        </strong>
                    </div>

                </div>

                {{-- PUBLICATION NOTICE AND ACTION --}}
                @if($publication)

                    <div class="publication-notice published-notice">
                        <i class="fas fa-info-circle"></i>

                        <span>
                            This term has already been published. Students can view
                            approved grades when this term matches the Active Term
                            in Admin Settings.
                        </span>
                    </div>

                @else

                    <div class="publication-notice">
                        <i class="fas fa-info-circle"></i>

                        <span>
                            Publishing this term makes its approved grades eligible
                            for student viewing. Students will only see grades when
                            this term matches the Active Term.
                        </span>
                    </div>

                    <div class="publication-action">

                        <button type="button" class="publish-button" onclick="openPublishModal()" {{ $approvedCount === 0 ? 'disabled' : '' }}>

                            <i class="fas fa-globe"></i>
                            Publish Grades
                        </button>

                        @if($approvedCount === 0)
                            <small>
                                No approved grades are available for publication.
                            </small>
                        @endif

                    </div>

                @endif

            </div>

        @else

            {{-- EMPTY SCHOOL YEAR STATE --}}
            <div class="empty-publication-state">
                <i class="fas fa-calendar-times"></i>
                <h3>No School Year Available</h3>
                <p>Create a school year first in Admin Settings.</p>
            </div>

        @endif


        {{-- =====================================================
        PUBLICATION HISTORY
        ====================================================== --}}

        <div class="publication-history-card">

            <div class="publication-history-header">

                <div>
                    <h2>Publication History</h2>
                    <p>View all previously published grades across school years.</p>
                </div>

                <span class="history-count">
                    {{ $publicationHistory->count() }}
                    {{ $publicationHistory->count() === 1 ? 'Publication' : 'Publications' }}
                </span>

            </div>

            @if($publicationHistory->isNotEmpty())

                <div class="publication-history-table-wrapper">

                    <table class="publication-history-table">

                        <thead>
                            <tr>
                                <th>School Year</th>
                                <th>Term</th>
                                <th>Published By</th>
                                <th>Publication Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach($publicationHistory as $history)

                                <tr>

                                    <td>
                                        <strong>{{ $history->school_year }}</strong>
                                    </td>

                                    <td>
                                        <span class="history-term">
                                            Term {{ $history->term }}
                                        </span>
                                    </td>

                                    <td>
                                        {{ $history->publisher?->name ?? 'Unknown Administrator' }}
                                    </td>

                                    <td>
                                        {{ $history->published_at?->format('M d, Y h:i A') ?? '—' }}
                                    </td>

                                    <td>
                                        <span class="publication-badge published">
                                            <i class="fas fa-check-circle"></i>
                                            Published
                                        </span>
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                <div class="history-empty-state">
                    <i class="fas fa-history"></i>

                    <h3>No Publication History</h3>

                    <p>
                        Published grades will appear here once the Administrator
                        publishes a term.
                    </p>
                </div>

            @endif

        </div>

    </div>


    {{-- =====================================================
    PUBLISH CONFIRMATION MODAL
    ====================================================== --}}

    @if($selectedSchoolYear && !$publications->has($selectedTerm) && $approvedCount > 0)

        <div class="publication-modal-overlay" id="publishModal">

            <div class="publication-modal">

                <div class="modal-icon">
                    <i class="fas fa-globe"></i>
                </div>

                <h2>Publish Grades?</h2>

                <p>
                    You are about to publish approved grades for:
                </p>

                <div class="modal-period">
                    {{ $selectedSchoolYear->year_name }}
                    - Term {{ $selectedTerm }}
                </div>

                <p class="modal-warning">
                    This action will make {{ number_format($approvedCount) }}
                    approved grade records eligible for student viewing.
                    This publication cannot be undone from this page.
                </p>

                <div class="modal-actions">

                    <button type="button" class="cancel-button" onclick="closePublishModal()">

                        Cancel
                    </button>

                    <form method="POST" action="{{ route('admin.grade-publications.publish', [
                'schoolYear' => $selectedSchoolYear->year_name,
                'term' => $selectedTerm
            ]) }}">

                        @csrf

                        <button type="submit" class="confirm-publish-button">
                            Confirm Publication
                        </button>

                    </form>

                </div>

            </div>

        </div>

    @endif

@endsection


{{-- =====================================================
PAGE JAVASCRIPT
====================================================== --}}

@section('page-js')

    <script>
        function openPublishModal() {
            document.getElementById('publishModal')?.classList.add('show');
        }

        function closePublishModal() {
            document.getElementById('publishModal')?.classList.remove('show');
        }

        document.addEventListener('click', function (event) {
            const modal = document.getElementById('publishModal');

            if (event.target === modal) {
                closePublishModal();
            }
        });
    </script>

@endsection