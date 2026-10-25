@extends('layouts.adviser')

@section('title', 'Announcements')

@section('header', 'Announcements')

@section('header-description', 'Stay updated with the latest announcements.')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/adviser/adviser-announcements.css') }}">
@endsection

@section('content')

    <div class="announcements-container">

        {{-- ANNOUNCEMENT LIST --}}
        <div class="announcements-section">

            <div class="section-title">
                <div>
                    <h2>Announcement List</h2>
                    <p>Stay updated with the latest announcements from the administration.</p>
                </div>
            </div>

            {{-- DISPLAY ANNOUNCEMENTS FROM DATABASE --}}
            @forelse ($announcements as $announcement)

                <div class="announcement-card">

                    {{-- ANNOUNCEMENT ICON --}}
                    <div class="announcement-icon">
                        <i class="fa-solid fa-bullhorn"></i>
                    </div>

                    <div class="announcement-content">

                        {{-- ANNOUNCEMENT HEADING --}}
                        <div class="announcement-top">

                            <div>
                                <span class="announcement-badge">
                                    {{ $announcement->audience === 'all' ? 'All Users' : 'Advisers' }}
                                </span>

                                <h3>{{ $announcement->title }}</h3>
                            </div>

                            <span class="announcement-date">
                                {{ $announcement->created_at
                ->timezone(config('app.timezone'))
                ->format('F j, Y') }}
                            </span>

                        </div>

                        {{-- ANNOUNCEMENT MESSAGE --}}
                        <p>{{ $announcement->message }}</p>

                        {{-- ANNOUNCEMENT FOOTER --}}
                        <div class="announcement-footer">

                            <span>
                                <i class="fa-solid fa-user-shield"></i>
                                Administration
                            </span>

                            <span>
                                <i class="fa-regular fa-clock"></i>
                                {{ $announcement->created_at
                ->timezone(config('app.timezone'))
                ->format('h:i A') }}
                            </span>

                        </div>

                    </div>

                </div>

            @empty

                {{-- EMPTY STATE --}}
                <div class="empty-announcements" id="emptyAnnouncements">

                    <div class="empty-icon">
                        <i class="fa-regular fa-bell"></i>
                    </div>

                    <h3>No announcements yet</h3>

                    <p>
                        There are currently no announcements from the administration.
                    </p>

                </div>

            @endforelse

        </div>

    </div>

@endsection

@section('page-js')
    <script src="{{ asset('js/adviser/adviser-announcements.js') }}"></script>
@endsection