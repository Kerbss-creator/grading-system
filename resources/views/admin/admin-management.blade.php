@extends('layouts.admin')

@section('title', 'Admin Management')
@section('header', 'Admin Management')
@section('header-description', 'Manage administrator accounts and account status.')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/admin/admin-management.css') }}">
@endsection

@section('content')
    <section class="admin-management">

        <!-- TOOLBAR -->
        <div class="admin-toolbar">
            <button type="button" class="add-admin-btn" id="addAdminBtn">
                <i class="fa-solid fa-plus"></i>
                Add Admin
            </button>

            <div class="toolbar-right">
                <div class="admin-search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="adminSearch" placeholder="Search admin...">
                </div>

                <select id="adminStatusFilter" class="admin-filter">
                    <option value="all">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>

        <!-- ADMIN TABLE -->
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Admin ID</th>
                        <th>Admin Name</th>
                        <th>Email Address</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody id="adminTableBody">

                    <!-- EMPTY STATE: SHOWN WHEN THERE ARE NO ADMIN RECORDS -->
                    <tr id="adminEmptyRow">
                        <td colspan="6">
                            <div class="admin-empty-state">
                                <div class="empty-icon">
                                    <i class="fa-solid fa-user-shield"></i>
                                </div>

                                <h3>No Admin Records Found</h3>
                                <p>There are currently no admin records to display.</p>

                                <button type="button" id="emptyAddAdminBtn">
                                    <i class="fa-solid fa-plus"></i>
                                    Add Admin
                                </button>
                            </div>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
    </section>


    <!-- ADD / EDIT ADMIN MODAL -->
    <div class="admin-modal-overlay" id="adminFormModal">
        <div class="admin-modal">
            <div class="admin-modal-header">
                <div>
                    <h2 id="adminFormTitle">Add Admin</h2>
                    <p id="adminFormDescription">Enter the administrator's information.</p>
                </div>

                <button type="button" class="admin-modal-close" data-close="adminFormModal" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="adminForm">
                <div class="admin-modal-body">

                    <div class="admin-form-group">
                        <label for="adminId">Admin ID</label>
                        <input type="text" id="adminId" placeholder="e.g. ADM-001" required>
                    </div>

                    <div class="admin-form-row">
                        <div class="admin-form-group">
                            <label for="adminFirstName">First Name</label>
                            <input type="text" id="adminFirstName" placeholder="Enter first name" required>
                        </div>

                        <div class="admin-form-group">
                            <label for="adminMiddleName">
                                Middle Name <span>(Optional)</span>
                            </label>
                            <input type="text" id="adminMiddleName" placeholder="Enter middle name">
                        </div>
                    </div>

                    <div class="admin-form-group">
                        <label for="adminLastName">Last Name</label>
                        <input type="text" id="adminLastName" placeholder="Enter last name" required>
                    </div>

                    <div class="admin-form-group">
                        <label for="adminEmail">Email Address</label>
                        <input type="email" id="adminEmail" placeholder="Enter email address" required>
                    </div>

                    <div class="admin-form-group">
                        <label for="adminAccountStatus">Account Status</label>
                        <select id="adminAccountStatus" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="admin-form-hint">
                        <i class="fa-solid fa-circle-info"></i>
                        Login credentials and database integration will be handled separately.
                    </div>
                </div>

                <div class="admin-modal-footer">
                    <button type="button" class="admin-cancel-btn" data-close="adminFormModal">Cancel</button>

                    <button type="submit" class="admin-save-btn">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span id="saveAdminText">Add Admin</span>
                    </button>
                </div>
            </form>
        </div>
    </div>


    <!-- VIEW ADMIN MODAL -->
    <div class="admin-modal-overlay" id="viewAdminModal">
        <div class="admin-modal admin-view-modal">
            <div class="admin-modal-header">
                <div>
                    <h2>Admin Information</h2>
                    <p>View administrator account details.</p>
                </div>

                <button type="button" class="admin-modal-close" data-close="viewAdminModal" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="admin-modal-body">
                <div class="admin-info-grid">
                    <div class="admin-info-item">
                        <span>Admin ID</span>
                        <strong id="viewAdminId">-</strong>
                    </div>

                    <div class="admin-info-item">
                        <span>Admin Name</span>
                        <strong id="viewAdminName">-</strong>
                    </div>

                    <div class="admin-info-item">
                        <span>Email Address</span>
                        <strong id="viewAdminEmail">-</strong>
                    </div>

                    <div class="admin-info-item">
                        <span>Role</span>
                        <strong>Admin</strong>
                    </div>

                    <div class="admin-info-item">
                        <span>Account Status</span>
                        <strong id="viewAdminStatus">-</strong>
                    </div>
                </div>
            </div>

            <div class="admin-modal-footer">
                <button type="button" class="admin-cancel-btn" data-close="viewAdminModal">Close</button>
            </div>
        </div>
    </div>


    <!-- DELETE CONFIRMATION MODAL -->
    <div class="admin-modal-overlay" id="deleteAdminModal">
        <div class="admin-modal admin-delete-modal">
            <div class="admin-modal-header">
                <div>
                    <h2>Delete Admin</h2>
                    <p>Confirm this action before proceeding.</p>
                </div>

                <button type="button" class="admin-modal-close" data-close="deleteAdminModal" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="admin-delete-body">
                <div class="admin-delete-icon">
                    <i class="fa-solid fa-trash"></i>
                </div>

                <h3>Delete this admin?</h3>
                <p>
                    Are you sure you want to delete
                    <strong id="deleteAdminName">this admin</strong>?
                </p>

                <p class="admin-delete-warning">
                    This action removes the record from the current page only.
                </p>
            </div>

            <div class="admin-modal-footer">
                <button type="button" class="admin-cancel-btn" data-close="deleteAdminModal">Cancel</button>

                <button type="button" class="admin-delete-confirm-btn" id="confirmDeleteAdmin">
                    <i class="fa-solid fa-trash"></i>
                    Delete
                </button>
            </div>
        </div>
    </div>


    <!-- STATUS CONFIRMATION MODAL -->
    <div class="admin-modal-overlay" id="adminStatusModal">
        <div class="admin-modal admin-status-modal">
            <div class="admin-modal-header">
                <div>
                    <h2 id="adminStatusTitle">Change Account Status</h2>
                    <p>Confirm the administrator's account status change.</p>
                </div>

                <button type="button" class="admin-modal-close" data-close="adminStatusModal" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="admin-delete-body">
                <div class="admin-status-icon" id="adminStatusIcon">
                    <i class="fa-solid fa-user-shield"></i>
                </div>

                <h3 id="adminStatusHeading">Change account status?</h3>
                <p id="adminStatusMessage">Confirm your choice.</p>
            </div>

            <div class="admin-modal-footer">
                <button type="button" class="admin-cancel-btn" data-close="adminStatusModal">Cancel</button>

                <button type="button" class="admin-status-confirm-btn" id="confirmAdminStatus">
                    Confirm
                </button>
            </div>
        </div>
    </div>
@endsection

@section('page-js')
    <script src="{{ asset('js/admin/admin-management.js') }}"></script>
@endsection