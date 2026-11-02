@extends('layouts.admin')

@section('title', 'Subject Management')
@section('header', 'Subject Management')
@section('header-description', 'View the predefined subjects offered by Forbes Academy.')

@section('content')

  <link rel="stylesheet" href="{{ asset('css/admin/subject-management-admin.css') }}">

  <div class="subject-management">

    {{-- SEARCH AND FILTERS --}}
    <div class="subject-toolbar">

      <div class="subject-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="subjectSearch" placeholder="Search subject..." aria-label="Search subjects">
      </div>

      <div class="subject-filters">

        <select id="gradeFilter" aria-label="Filter by grade level">
          <option value="">All Grade Levels</option>
          <option value="7">Grade 7</option>
          <option value="8">Grade 8</option>
          <option value="9">Grade 9</option>
          <option value="10">Grade 10</option>
        </select>

        <select id="statusFilter" aria-label="Filter by status">
          <option value="">All Statuses</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>

      </div>
    </div>

    {{-- SUBJECT TABLE --}}
    <div class="subject-table-container">

      <table class="subject-table">

        <thead>
          <tr>
            <th>Subject Name</th>
            <th>Grade Levels</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>

        <tbody id="subjectTableBody">

          {{-- Sample predefined subjects --}}
          <tr data-grades="7,8,9,10" data-status="active">
            <td>
              <div class="subject-name">
                <span class="subject-icon">
                  <i class="fa-solid fa-calculator"></i>
                </span>
                <span>Mathematics</span>
              </div>
            </td>
            <td>Grades 7–10</td>
            <td><span class="status-badge active">Active</span></td>
            <td>
              <button type="button" class="view-btn" data-subject="Mathematics" aria-label="View Mathematics details"
                title="View Details">
                <i class="fa-solid fa-arrow-right"></i>
              </button>
            </td>
          </tr>

          <tr data-grades="7,8,9,10" data-status="active">
            <td>
              <div class="subject-name">
                <span class="subject-icon">
                  <i class="fa-solid fa-flask"></i>
                </span>
                <span>Science</span>
              </div>
            </td>
            <td>Grades 7–10</td>
            <td><span class="status-badge active">Active</span></td>
            <td>
              <button type="button" class="view-btn" data-subject="Science" aria-label="View Science details"
                title="View Details">
                <i class="fa-solid fa-arrow-right"></i>
              </button>
            </td>
          </tr>

          <tr data-grades="7,8,9,10" data-status="active">
            <td>
              <div class="subject-name">
                <span class="subject-icon">
                  <i class="fa-solid fa-book-open"></i>
                </span>
                <span>English</span>
              </div>
            </td>
            <td>Grades 7–10</td>
            <td><span class="status-badge active">Active</span></td>
            <td>
              <button type="button" class="view-btn" data-subject="English" aria-label="View English details"
                title="View Details">
                <i class="fa-solid fa-arrow-right"></i>
              </button>
            </td>
          </tr>

          <tr data-grades="7,8,9,10" data-status="active">
            <td>
              <div class="subject-name">
                <span class="subject-icon">
                  <i class="fa-solid fa-language"></i>
                </span>
                <span>Filipino</span>
              </div>
            </td>
            <td>Grades 7–10</td>
            <td><span class="status-badge active">Active</span></td>
            <td>
              <button type="button" class="view-btn" data-subject="Filipino" aria-label="View Filipino details"
                title="View Details">
                <i class="fa-solid fa-arrow-right"></i>
              </button>
            </td>
          </tr>

          <tr data-grades="7,8,9,10" data-status="active">
            <td>
              <div class="subject-name">
                <span class="subject-icon">
                  <i class="fa-solid fa-earth-asia"></i>
                </span>
                <span>Araling Panlipunan</span>
              </div>
            </td>
            <td>Grades 7–10</td>
            <td><span class="status-badge active">Active</span></td>
            <td>
              <button type="button" class="view-btn" data-subject="Araling Panlipunan"
                aria-label="View Araling Panlipunan details" title="View Details">
                <i class="fa-solid fa-arrow-right"></i>
              </button>
            </td>
          </tr>

          <tr data-grades="7,8,9,10" data-status="active">
            <td>
              <div class="subject-name">
                <span class="subject-icon">
                  <i class="fa-solid fa-screwdriver-wrench"></i>
                </span>
                <span>TLE</span>
              </div>
            </td>
            <td>Grades 7–10</td>
            <td><span class="status-badge active">Active</span></td>
            <td>
              <button type="button" class="view-btn" data-subject="TLE" aria-label="View TLE details"
                title="View Details">
                <i class="fa-solid fa-arrow-right"></i>
              </button>
            </td>
          </tr>

          <tr data-grades="7,8,9,10" data-status="active">
            <td>
              <div class="subject-name">
                <span class="subject-icon">
                  <i class="fa-solid fa-music"></i>
                </span>
                <span>MAPEH</span>
              </div>
            </td>
            <td>Grades 7–10</td>
            <td><span class="status-badge active">Active</span></td>
            <td>
              <button type="button" class="view-btn" data-subject="MAPEH" aria-label="View MAPEH details"
                title="View Details">
                <i class="fa-solid fa-arrow-right"></i>
              </button>
            </td>
          </tr>

          <tr data-grades="7,8,9,10" data-status="active">
            <td>
              <div class="subject-name">
                <span class="subject-icon">
                  <i class="fa-solid fa-heart"></i>
                </span>
                <span>Values Education</span>
              </div>
            </td>
            <td>Grades 7–10</td>
            <td><span class="status-badge active">Active</span></td>
            <td>
              <button type="button" class="view-btn" data-subject="Values Education"
                aria-label="View Values Education details" title="View Details">
                <i class="fa-solid fa-arrow-right"></i>
              </button>
            </td>
          </tr>

        </tbody>
      </table>

      {{-- Empty state --}}
      <div class="subject-empty-state" id="subjectEmptyState" hidden>
        <i class="fa-solid fa-book-open"></i>
        <h3>No Subjects Found</h3>
        <p>Try changing your search or filter selections.</p>
      </div>

    </div>

    {{-- Total count --}}
    <div class="subject-table-footer">
      <span id="subjectCount">Showing 8 subjects</span>
    </div>

  </div>

@endsection

@section('page-js')
  <script src="{{ asset('js/admin/subject-management-admin.js') }}"></script>
@endsection