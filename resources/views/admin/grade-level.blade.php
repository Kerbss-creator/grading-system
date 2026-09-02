@extends('layouts.admin')

@section('title', 'Grade Level Management')

@section('header', 'Grade Level Management')

@section('header-description', 'Manage grade levels, sections, and assigned advisers.')

@section('page-css')

  <link rel="stylesheet" href="{{ asset('css/grade-level-admin.css') }}">
@endsection

@section('content')

  <!-- PAGE HEADER -->

  <div class="page-header">

    
    

    <button class="add-grade-btn" id="addGradeBtn" type="button">
      <i class="fa-solid fa-plus"></i>
      Add Grade Level
    </button>
    

  </div>

  <!-- SUMMARY -->

  <div class="summary-container">

    
    <div class="summary-card">

      <div class="summary-icon">
        <i class="fa-solid fa-school"></i>
      </div>

      <div>
        <span>Total Grade Levels</span>
        <h2 id="totalGrades">4</h2>
      </div>

    </div>


    <div class="summary-card">

      <div class="summary-icon">
        <i class="fa-solid fa-layer-group"></i>
      </div>

      <div>
        <span>Total Sections</span>
        <h2 id="totalSections">16</h2>
      </div>

    </div>


    <div class="summary-card">

      <div class="summary-icon">
        <i class="fa-solid fa-user-graduate"></i>
      </div>

      <div>
        <span>Total Students</span>
        <h2 id="totalStudents">668</h2>
      </div>

    </div>

  </div>

  <!-- GRADE LEVELS -->

  <div class="section-header">

    <div>
      <h2>Grade Levels</h2>
      <p>Select a grade level to manage its sections.</p>
    </div>
    

  </div>

  <!-- GRADE CARDS -->

  <div class="grade-grid" id="gradeGrid">

    <!-- GRADE 7 -->
    <div class="grade-card" data-grade="Grade 7">

      <div class="grade-card-top">

        <div class="grade-icon">
          <span>7</span>
        </div>

        <span class="grade-status active">
          Active
        </span>

      </div>


      <div class="grade-info">

        <h2>Grade 7</h2>

        <p>Junior High School</p>

      </div>


      <div class="grade-details">

        <div class="detail-item">

          <i class="fa-solid fa-layer-group"></i>

          <div>
            <span>Sections</span>
            <strong class="section-count">4</strong>
          </div>

        </div>


        <div class="detail-item">

          <i class="fa-solid fa-user-graduate"></i>

          <div>
            <span>Students</span>
            <strong class="student-count">168</strong>
          </div>

        </div>

      </div>


      <div class="grade-footer">

        <button class="view-btn" type="button">
          <i class="fa-solid fa-eye"></i>
          View
        </button>

        <button class="edit-btn" type="button">
          <i class="fa-solid fa-pen"></i>
          Edit
        </button>

        <button class="delete-btn" type="button">
          <i class="fa-solid fa-trash"></i>
          Delete
        </button>

      </div>

    </div>


    <!-- GRADE 8 -->
    <div class="grade-card" data-grade="Grade 8">

      <div class="grade-card-top">

        <div class="grade-icon">
          <span>8</span>
        </div>

        <span class="grade-status active">
          Active
        </span>

      </div>


      <div class="grade-info">

        <h2>Grade 8</h2>

        <p>Junior High School</p>

      </div>


      <div class="grade-details">

        <div class="detail-item">

          <i class="fa-solid fa-layer-group"></i>

          <div>
            <span>Sections</span>
            <strong class="section-count">4</strong>
          </div>

        </div>


        <div class="detail-item">

          <i class="fa-solid fa-user-graduate"></i>

          <div>
            <span>Students</span>
            <strong class="student-count">167</strong>
          </div>

        </div>

      </div>


      <div class="grade-footer">

        <button class="view-btn" type="button">
          <i class="fa-solid fa-eye"></i>
          View
        </button>

        <button class="edit-btn" type="button">
          <i class="fa-solid fa-pen"></i>
          Edit
        </button>

        <button class="delete-btn" type="button">
          <i class="fa-solid fa-trash"></i>
          Delete
        </button>

      </div>

    </div>


    <!-- GRADE 9 -->
    <div class="grade-card" data-grade="Grade 9">

      <div class="grade-card-top">

        <div class="grade-icon">
          <span>9</span>
        </div>

        <span class="grade-status active">
          Active
        </span>

      </div>


      <div class="grade-info">

        <h2>Grade 9</h2>

        <p>Junior High School</p>

      </div>


      <div class="grade-details">

        <div class="detail-item">

          <i class="fa-solid fa-layer-group"></i>

          <div>
            <span>Sections</span>
            <strong class="section-count">4</strong>
          </div>

        </div>


        <div class="detail-item">

          <i class="fa-solid fa-user-graduate"></i>

          <div>
            <span>Students</span>
            <strong class="student-count">166</strong>
          </div>

        </div>

      </div>


      <div class="grade-footer">

        <button class="view-btn" type="button">
          <i class="fa-solid fa-eye"></i>
          View
        </button>

        <button class="edit-btn" type="button">
          <i class="fa-solid fa-pen"></i>
          Edit
        </button>

        <button class="delete-btn" type="button">
          <i class="fa-solid fa-trash"></i>
          Delete
        </button>

      </div>

    </div>


    <!-- GRADE 10 -->
    <div class="grade-card" data-grade="Grade 10">

      <div class="grade-card-top">

        <div class="grade-icon">
          <span>10</span>
        </div>

        <span class="grade-status active">
          Active
        </span>

      </div>


      <div class="grade-info">

        <h2>Grade 10</h2>

        <p>Junior High School</p>

      </div>


      <div class="grade-details">

        <div class="detail-item">

          <i class="fa-solid fa-layer-group"></i>

          <div>
            <span>Sections</span>
            <strong class="section-count">4</strong>
          </div>

        </div>


        <div class="detail-item">

          <i class="fa-solid fa-user-graduate"></i>

          <div>
            <span>Students</span>
            <strong class="student-count">167</strong>
          </div>

        </div>

      </div>


      <div class="grade-footer">

        <button class="view-btn" type="button">
          <i class="fa-solid fa-eye"></i>
          View
        </button>

        <button class="edit-btn" type="button">
          <i class="fa-solid fa-pen"></i>
          Edit
        </button>

        <button class="delete-btn" type="button">
          <i class="fa-solid fa-trash"></i>
          Delete
        </button>

      </div>

    </div>
  

  </div>

  <!-- ADD / EDIT MODAL -->

  <div class="modal-overlay" id="gradeModal">

   
    <div class="grade-modal">

      <div class="modal-header">

        <div>

          <h2 id="modalTitle">
            Add Grade Level
          </h2>

          <p id="modalDescription">
            Create a new grade level.
          </p>

        </div>

        <button class="close-modal" id="closeGradeModal" type="button">

          <i class="fa-solid fa-xmark"></i>

        </button>

      </div>


      <div class="modal-body">

        <div class="input-group">

          <label for="gradeLevel">
            Grade Level
          </label>

          <select id="gradeLevel">

            <option value="">
              Select Grade Level
            </option>

            <option value="Grade 7">
              Grade 7
            </option>

            <option value="Grade 8">
              Grade 8
            </option>

            <option value="Grade 9">
              Grade 9
            </option>

            <option value="Grade 10">
              Grade 10
            </option>

          </select>

        </div>


        <div class="input-group">

          <label for="gradeStatus">
            Status
          </label>

          <select id="gradeStatus">

            <option value="Active">
              Active
            </option>

            <option value="Inactive">
              Inactive
            </option>

          </select>

        </div>

      </div>


      <div class="modal-footer">

        <button class="cancel-btn" id="cancelGrade" type="button">

          Cancel

        </button>

        <button class="save-btn" id="saveGrade" type="button">

          <i class="fa-solid fa-check"></i>

          <span id="saveButtonText">
            Save Grade Level
          </span>

        </button>

      </div>

    </div>
   

  </div>

  <!-- VIEW MODAL -->

  <div class="modal-overlay" id="viewModal">

   
    <div class="grade-modal view-modal">

      <div class="modal-header">

        <div>

          <h2 id="viewGradeTitle">
            Grade 7
          </h2>

          <p>
            Grade level information
          </p>

        </div>

        <button class="close-modal" id="closeViewModal" type="button">

          <i class="fa-solid fa-xmark"></i>

        </button>

      </div>


      <div class="view-body">

        <div class="view-item">

          <span>Grade Level</span>

          <strong id="viewGradeName">
            Grade 7
          </strong>

        </div>


        <div class="view-item">

          <span>School Level</span>

          <strong>
            Junior High School
          </strong>

        </div>


        <div class="view-item">

          <span>Number of Sections</span>

          <strong id="viewSectionCount">
            4
          </strong>

        </div>


        <div class="view-item">

          <span>Total Students</span>

          <strong id="viewStudentCount">
            168
          </strong>

        </div>


        <div class="view-item">

          <span>Status</span>

          <strong id="viewStatus" class="view-active">

            Active

          </strong>

        </div>

      </div>


      <div class="modal-footer">

        <button class="cancel-btn" id="closeViewButton" type="button">

          Close

        </button>

      </div>

    </div>


  </div>

@endsection

@section('page-js')

  <script src="{{ asset('js/grade-level-admin.js') }}"></script>

@endsection