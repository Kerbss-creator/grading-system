@extends('layouts.admin')

@section('title', 'Announcements')
@section('header', 'Announcement')
@section('header-description', 'Create and manage announcements for teachers and students.')

@section('page-css')
  <link rel="stylesheet" href="{{ asset('css/admin/announcement-admin.css') }}">



@endsection

@section('content')

  <section class="announcement-container">

    {{-- PAGE TOP --}}
    <div class="section-top">

      <div>
        <h2>Announcement List</h2>
      </div>

      <div class="section-actions">
        <button type="button" class="add-btn" id="addAnnouncementBtn">
          <i class="fa-solid fa-plus"></i>
          Create Announcement
        </button>
      </div>

    </div>

    {{-- FILTERS --}}
    <div class="announcement-filters">

      <button type="button" class="filter-btn active" data-filter="all">
        All
      </button>

      <button type="button" class="filter-btn" data-filter="advisers">
        Advisers
      </button>

      <button type="button" class="filter-btn" data-filter="teachers">
        Teachers
      </button>

      <button type="button" class="filter-btn" data-filter="students">
        Students
      </button>

    </div>

    {{-- ANNOUNCEMENT LIST --}}
    <div class="announcement-list" id="announcementList">

      {{-- DEFAULT EMPTY STATE --}}
      <div class="announcement-empty-state" id="announcementEmptyState">

        <div class="empty-state-icon">
          <i class="fa-solid fa-bullhorn"></i>
        </div>

        <h3>No Announcement Yet</h3>

        <p>
          There are no announcements yet.
          Create your first announcement to share
          important school updates.
        </p>

      </div>

      {{-- Announcement cards will be added here by JavaScript --}}

    </div>

  </section>

  {{-- CREATE / EDIT MODAL --}}
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

      <form id="announcementForm" action="{{ route('admin.announcement.store') }}" method="POST">

        @csrf

        <div class="form-group">
          <label for="announcementTitle">
            Announcement Title
          </label>

          <input type="text" id="announcementTitle" name="title" placeholder="Enter announcement title" required>
        </div>

        <div class="form-group">
          <label for="announcementAudience">
            Audience
          </label>

          <select id="announcementAudience" name="audience" required>
            <option value="">Select audience</option>
            <option value="all">All Users</option>
            <option value="advisers">Advisers</option>
            <option value="teachers">Teachers</option>
            <option value="students">Students</option>
          </select>
        </div>

        <div class="form-group">
          <label for="announcementMessage">
            Announcement Message
          </label>

          <textarea id="announcementMessage" name="message" placeholder="Write your announcement here..."
            required></textarea>
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

    </div>

  </div>

  {{-- VIEW MODAL --}}
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
          <h2 id="viewTitle">Announcement Title</h2>
          <p id="viewDate">Announcement date</p>
        </div>

      </div>

      <div class="view-details">

        <div>
          <span>Audience</span>
          <strong id="viewAudience">All Users</strong>
        </div>

        <div>
          <span>Date Published</span>
          <strong id="viewDateDetails">Announcement date</strong>
        </div>

      </div>

      <div class="message-box">
        <span>Message</span>
        <p id="viewMessage">Announcement message.</p>
      </div>

      <div class="modal-actions">
        <button type="button" class="cancel-btn" id="closeViewBtn">
          Close
        </button>
      </div>

    </div>

  </div>

  {{-- DELETE MODAL --}}
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


  {{-- SUCCESS MODAL --}}
  <div class="success-modal-overlay" id="successModal">

    <div class="success-modal">

      <div class="success-modal-icon">
        <i class="fa-solid fa-circle-check"></i>
      </div>

      <h2 id="successModalTitle">Successfully Added!</h2>

      <p id="successModalMessage">
        Your announcement has been added successfully.
      </p>

      <button type="button" id="successModalButton">
        OK, Got it
      </button>
    </div>
  </div>


@endsection

@section('page-js')
  <script>
    window.savedAnnouncements = @json($announcements);
  </script>

  <script src="{{ asset('js/admin/announcement-admin.js') }}"></script>
@endsection