```blade
@extends('layouts.admin')

@section('title', 'Adviser Management')
@section('header', 'Adviser Management')
@section('header-description', 'Manage adviser accounts and assigned advisory classes.')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/admin/adviser-management.css') }}">
@endsection

@section('content')

    <section class="adviser-management">

        {{-- TOOLBAR --}}
        <div class="adviser-toolbar">

            <button class="add-adviser-btn" type="button" id="addAdviserBtn">
                <i class="fa-solid fa-plus"></i>
                Add Adviser
            </button>

            <div class="toolbar-right">

                {{-- SEARCH --}}
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="adviserSearch" placeholder="Search adviser...">
                </div>

                {{-- GRADE FILTER --}}
                <select class="filter-select" id="gradeFilter">
                    <option value="all">All Grades</option>
                    <option value="Grade 7">Grade 7</option>
                    <option value="Grade 8">Grade 8</option>
                    <option value="Grade 9">Grade 9</option>
                    <option value="Grade 10">Grade 10</option>
                </select>

                {{-- STATUS FILTER --}}
                <select class="filter-select" id="statusFilter">
                    <option value="all">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>

            </div>
        </div>

        {{-- ADVISER TABLE --}}
        <div class="table-container">
            <table class="adviser-table">

                <thead>
                    <tr>
                        <th>Adviser ID</th>
                        <th>Adviser Name</th>
                        <th>Email Address</th>
                        <th>Advisory Class</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody id="adviserTableBody">

                    {{-- Wala pang database integration.
                    Ang rows ay idaragdag muna ng JavaScript. --}}

                    <tr class="empty-state-row" id="adviserEmptyRow">
                        <td colspan="6">
                            <div class="adviser-empty-state">

                                <div class="empty-state-icon">
                                    <i class="fa-solid fa-chalkboard-user"></i>
                                </div>

                                <h3>No Adviser Records Yet</h3>

                                <p>
                                    There are no adviser records to display.
                                    Add your first adviser to get started.
                                </p>

                                <button type="button" class="empty-state-add-btn" id="emptyAddAdviserBtn">

                                    <i class="fa-solid fa-plus"></i>
                                    Add Adviser

                                </button>

                            </div>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>

        <p class="table-note">
            Adviser records are currently in demo mode and are not saved to the database.
        </p>

    </section>


    {{-- ADD / EDIT ADVISER MODAL --}}
    <div class="modal-overlay" id="adviserFormModal">

        <div class="adviser-modal">

            <div class="modal-header">
                <div>
                    <h2 id="adviserFormTitle">Add Adviser</h2>
                    <p id="adviserFormDescription">
                        Create an adviser record and assign an advisory class.
                    </p>
                </div>

                <button class="modal-close" type="button" data-close="adviserFormModal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="adviserForm">

                <div class="modal-body">

                    <div class="form-group">
                        <label for="adviserId">Adviser ID</label>
                        <input type="text" id="adviserId" placeholder="e.g. ADV-0001" required>
                    </div>

                    <div class="form-row">

                        <div class="form-group">
                            <label for="adviserFirstName">First Name</label>
                            <input type="text" id="adviserFirstName" placeholder="First name" required>
                        </div>

                        <div class="form-group">
                            <label for="adviserMiddleName">
                                Middle Name <span>(Optional)</span>
                            </label>
                            <input type="text" id="adviserMiddleName" placeholder="Middle name">
                        </div>

                    </div>

                    <div class="form-group">
                        <label for="adviserLastName">Last Name</label>
                        <input type="text" id="adviserLastName" placeholder="Last name" required>
                    </div>

                    <div class="form-group">
                        <label for="adviserEmail">Email Address</label>
                        <input type="email" id="adviserEmail" placeholder="Enter email address" required>
                    </div>

                    <div class="form-group">
                        <label for="advisoryClass">Assigned Advisory Class</label>

                        <select id="advisoryClass" required>
                            <option value="">Select Advisory Class</option>
                            <option value="Grade 7-A">Grade 7-A</option>
                            <option value="Grade 7-B">Grade 7-B</option>
                            <option value="Grade 7-C">Grade 7-C</option>
                            <option value="Grade 8-A">Grade 8-A</option>
                            <option value="Grade 8-B">Grade 8-B</option>
                            <option value="Grade 8-C">Grade 8-C</option>
                            <option value="Grade 9-A">Grade 9-A</option>
                            <option value="Grade 9-B">Grade 9-B</option>
                            <option value="Grade 9-C">Grade 9-C</option>
                            <option value="Grade 10-A">Grade 10-A</option>
                            <option value="Grade 10-B">Grade 10-B</option>
                            <option value="Grade 10-C">Grade 10-C</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="adviserStatus">Status</label>
                        <select id="adviserStatus">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <p class="form-hint">
                        <i class="fa-solid fa-circle-info"></i>
                        Demo only. Account creation and login permissions
                        will be connected to Laravel later.
                    </p>

                </div>

                <div class="modal-footer">

                    <button type="button" class="cancel-btn" data-close="adviserFormModal">
                        Cancel
                    </button>

                    <button type="submit" class="save-adviser-btn">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span id="saveAdviserText">Add Adviser</span>
                    </button>

                </div>

            </form>
        </div>
    </div>


    {{-- VIEW ADVISER MODAL --}}
    <div class="modal-overlay" id="viewAdviserModal">

        <div class="adviser-modal view-modal">

            <div class="modal-header">
                <div>
                    <h2>Adviser Information</h2>
                    <p>View adviser record details.</p>
                </div>

                <button class="modal-close" type="button" data-close="viewAdviserModal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-body">

                <div class="adviser-info">

                    <div class="info-group">
                        <span>Adviser ID</span>
                        <strong id="viewAdviserId">-</strong>
                    </div>

                    <div class="info-group">
                        <span>Full Name</span>
                        <strong id="viewAdviserName">-</strong>
                    </div>

                    <div class="info-group">
                        <span>Email Address</span>
                        <strong id="viewAdviserEmail">-</strong>
                    </div>

                    <div class="info-group">
                        <span>Advisory Class</span>
                        <strong id="viewAdvisoryClass">-</strong>
                    </div>

                    <div class="info-group">
                        <span>Status</span>
                        <strong id="viewAdviserStatus">-</strong>
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="cancel-btn" data-close="viewAdviserModal">
                    Close
                </button>
            </div>

        </div>
    </div>


    {{-- DELETE ADVISER MODAL --}}
    <div class="modal-overlay" id="deleteAdviserModal">

        <div class="adviser-modal delete-modal">

            <div class="modal-header">
                <div>
                    <h2>Delete Adviser</h2>
                    <p>Confirm before removing this adviser.</p>
                </div>

                <button class="modal-close" type="button" data-close="deleteAdviserModal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="delete-body">

                <div class="delete-icon">
                    <i class="fa-solid fa-trash"></i>
                </div>

                <h3>Delete this adviser?</h3>

                <p>
                    Are you sure you want to remove
                    <strong id="deleteAdviserName">this adviser</strong>?
                </p>

                <p class="delete-warning">
                    Demo mode only. No database record will be deleted.
                </p>

            </div>

            <div class="modal-footer">

                <button type="button" class="cancel-btn" data-close="deleteAdviserModal">
                    Cancel
                </button>

                <button type="button" class="delete-confirm-btn" id="confirmDeleteAdviser">
                    <i class="fa-solid fa-trash"></i>
                    Delete
                </button>

            </div>

        </div>
    </div>

@endsection

@section('page-js')
    <script src="{{ asset('js/admin/adviser-management.js') }}"></script>
@endsection
```