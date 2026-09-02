@extends('layouts.admin')

@section('title', 'Announcements')

@section('header', 'Announcement')

@section('header-description', 'Manage and publish important school updates, notices, and reminders.')

@section('page-css')

  <link rel="stylesheet" href="{{ asset('css/announcement-admin.css') }}">
@endsection

@section('content')

  <!-- ANNOUNCEMENT CONTAINER -->

  <section class="announcement-container">


    <div class="section-top">

      <h2>Announcement List</h2>

      <div class="section-actions">

        <button type="button" class="see-all-btn" id="seeAllBtn">
          See All
        </button>

        <button type="button" class="add-btn" id="addAnnouncementBtn">
          <i class="fa-solid fa-plus"></i>
          Create Announcement
        </button>

      </div>

    </div>

    <!-- FILTERS -->

    <div class="announcement-filters">

      <button type="button" class="filter-btn active" data-filter="all">
        All
      </button>

      <button type="button" class="filter-btn" data-filter="teacher">
        Teachers
      </button>

      <button type="button" class="filter-btn" data-filter="student">
        Students
      </button>

    </div>

    <!-- ANNOUNCEMENT LIST -->

    <div class="announcement-list" id="announcementList">

      <!-- ANNOUNCEMENT 1 -->

      <div class="announcement-card" data-audience="all" data-title="Welcome to the New School Year"
        data-message="Welcome to Forbes Academy. We wish everyone a successful and productive school year."
        data-date="August 27, 2026">

        <div class="announcement-icon">
          <i class="fa-solid fa-bullhorn"></i>
        </div>

        <div class="announcement-content">

          <div class="announcement-heading">

            <h3>Welcome to the New School Year</h3>

            <span class="announcement-tag all">
              All Users
            </span>

          </div>

          <p>
            Welcome to Forbes Academy. We wish everyone a successful
            and productive school year.
          </p>

        </div>

        <div class="announcement-actions">

          <button type="button" class="edit-btn" title="Edit">
            <i class="fa-solid fa-pen"></i>
          </button>

          <button type="button" class="delete-btn" title="Delete">
            <i class="fa-solid fa-trash"></i>
          </button>

        </div>

      </div>

      <!-- ANNOUNCEMENT 2 -->

      <div class="announcement-card" data-audience="teacher" data-title="Grade Submission Reminder"
        data-message="Teachers are reminded to submit and finalize student grades before the deadline."
        data-date="August 26, 2026">

        <div class="announcement-icon">
          <i class="fa-solid fa-chalkboard-user"></i>
        </div>

        <div class="announcement-content">

          <div class="announcement-heading">

            <h3>Grade Submission Reminder</h3>

            <span class="announcement-tag teacher">
              Teachers
            </span>

          </div>

          <p>
            Teachers are reminded to submit and finalize student grades
            before the deadline.
          </p>

        </div>

        <div class="announcement-actions">

          <button type="button" class="edit-btn" title="Edit">
            <i class="fa-solid fa-pen"></i>
          </button>

          <button type="button" class="delete-btn" title="Delete">
            <i class="fa-solid fa-trash"></i>
          </button>

        </div>

      </div>

      <!-- ANNOUNCEMENT 3 -->

      <div class="announcement-card" data-audience="student" data-title="Viewing of Final Grades"
        data-message="Students may now view their latest approved grades through the student portal."
        data-date="August 25, 2026">

        <div class="announcement-icon">
          <i class="fa-solid fa-user-graduate"></i>
        </div>

        <div class="announcement-content">

          <div class="announcement-heading">

            <h3>Viewing of Final Grades</h3>

            <span class="announcement-tag student">
              Students
            </span>

          </div>

          <p>
            Students may now view their latest approved grades through
            the student portal.
          </p>

        </div>

        <div class="announcement-actions">

          <button type="button" class="edit-btn" title="Edit">
            <i class="fa-solid fa-pen"></i>
          </button>

          <button type="button" class="delete-btn" title="Delete">
            <i class="fa-solid fa-trash"></i>
          </button>

        </div>

      </div>

    </div>


  </section>

  <!-- CREATE / EDIT ANNOUNCEMENT MODAL -->

  <div class="modal" id="announcementModal">


    <div class="modal-box">

      <div class="modal-header">

        <div>

          <h2 id="modalTitle">Create Announcement</h2>

          <p>Create an announcement for users.</p>

        </div>

        <button type="button" id="closeAnnouncementModal">

          <i class="fa-solid fa-xmark"></i>

        </button>

      </div>

      <form id="announcementForm">

        <div class="form-group">

          <label for="announcementTitle">
            Announcement Title
          </label>

          <input type="text" id="announcementTitle" placeholder="Enter announcement title" required>

        </div>

        <div class="form-group">

          <label for="announcementAudience">
            Audience
          </label>

          <select id="announcementAudience" required>

            <option value="">
              Select audience
            </option>

            <option value="all">
              All Users
            </option>

            <option value="teacher">
              Teachers
            </option>

            <option value="student">
              Students
            </option>

          </select>

        </div>

        <div class="form-group">

          <label for="announcementMessage">
            Announcement Message
          </label>

          <textarea id="announcementMessage" placeholder="Write your announcement here..." required></textarea>

        </div>

        <div class="modal-actions">

          <button type="button" class="cancel-btn" id="cancelAnnouncement">
            Cancel
          </button>

          <button type="submit" class="save-btn">

            <i class="fa-solid fa-check"></i>

            Save Announcement

          </button>

        </div>

      </form>

    </div </div>

    <!-- VIEW ANNOUNCEMENT MODAL -->

    <div class="modal" id="viewModal">


      <div class="modal-box view-box">

        <div class="modal-header">

          <div>

            <h2>Announcement Details</h2>

            <p>View announcement information.</p>

          </div>

          <button type="button" id="closeViewModal">

            <i class="fa-solid fa-xmark"></i>

          </button>

        </div>

        <div class="view-title">

          <div class="large-view-icon">

            <i class="fa-solid fa-bullhorn"></i>

          </div>

          <div>

            <h2 id="viewTitle">
              Announcement Title
            </h2>

            <p id="viewDate">
              August 27, 2026
            </p>

          </div>

        </div>

        <div class="view-details">

          <div>

            <span>Audience</span>

            <strong id="viewAudience">
              All Users
            </strong>

          </div>

          <div>

            <span>Date Published</span>

            <strong id="viewDateDetails">
              August 27, 2026
            </strong>

          </div>

        </div>

        <div class="message-box">

          <span>Message</span>

          <p id="viewMessage">
            Announcement message.
          </p>

        </div>

        <div class="modal-actions">

          <button type="button" class="cancel-btn" id="closeViewBtn">
            Close
          </button>

        </div>

      </div>

    </div>

    <!-- DELETE MODAL -->

    <div class="modal" id="deleteModal">
      <div class="modal-box delete-box">

        <div class="modal-header">

          <div>

            <h2>Delete Announcement</h2>

            <p>This action cannot be undone.</p>

          </div>

          <button type="button" id="closeDeleteModal">

            <i class="fa-solid fa-xmark"></i>

          </button>

        </div>

        <div class="delete-content">

          <div class="delete-icon">

            <i class="fa-solid fa-trash"></i>

          </div>

          <h3>Delete this announcement?</h3>

          <p>

            Are you sure you want to delete

            <strong id="deleteAnnouncementTitle">
              this announcement
            </strong>?

          </p>

        </div>

        <div class="modal-actions">

          <button type="button" class="cancel-btn" id="cancelDelete">
            Cancel
          </button>

          <button type="button" class="delete-confirm-btn" id="confirmDelete">

            <i class="fa-solid fa-trash"></i>

            Delete

          </button>

        </div>

      </div>

    </div>

@endsection

  @section('page-js')

    <script src="{{ asset('js/announcement-admin.js') }}"></script>

  @endsection