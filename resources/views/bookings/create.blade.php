@extends('layouts.app')

@section('head')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.min.css" integrity="sha512-f0tzWhCwVFS3WeYaofoLWkTP62ObhewQ1EZn65oSYDZUg1+CyywGKkWzm8BxaJj5HGKI72PnMH9jYyIFz+GH7g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="/css/bookings/create.css">

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.min.js" integrity="sha512-AIOTidJAcHBH2G/oZv9viEGXRqDNmfdPVPYOYKGy3fti0xIplnlgMHUGfuNRzC6FkzIo0iIxgFnr9RikFxK+sw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
const isAuth = {{ Auth::check() ? 'true' : 'false' }};
const roomId = {{ $roomId }};
const bookingsDate = "{{ session('google_bookings_date') }}";
const isGoogleCallback = {{ session('google_bookings_user_id') && session('google_bookings_date')
 && session('google_access_token') && session('google_bookings_room_id') === strval($roomId) ? 'true' : 'false' }};

const listUrl = "{{ route('bookings.list') }}";
const roomListUrl = "{{ route('rooms.list') }}";
const loginUrl = "{{ route('bookings.login') }}";
const storeUrl = "{{ route('bookings.store') }}";
const destroyUrl = "{{ route('bookings.destroy') }}";
const googleLoginUrl = "{{ route('google.login') }}";
const resetSessionUrl = "{{ route('bookings.reset-session') }}";
const roomAvailableUrl = "{{ route('bookings.room-available', $roomId) }}";

let isOfficeMode = {{ $officeMode ? 'true' : 'false' }};
</script>
<script src="/js/bookings/create.js"></script>
@endsection

@section('navbar-title'){{ $room->name }}@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-7 room-card" id="room-status">
            <div class="row">
                <div class="col-md-8">
                    <div id="current-date"></div>
                    <div id="current-time"></div>
                </div>
                <div class="col-md-4">
                    <div id="current-available">Status: <span id="current-available-status"></span></div>
                </div>
            </div>
        </div>
        <div class="col-md-5 room-card">
            <div id="current-bookings">
                <h4>Jam Penggunaan Hari Ini:</h4>
            </div>
        </div>
    </div>

    <div id="calendar" class="mt-3"></div>
</div>

<!-- Modal untuk Histori Booking -->
<div class="modal fade" id="bookingHistoryModal" tabindex="-1" role="dialog" aria-labelledby="bookingHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bookingHistoryModalLabel">Histori Peminjaman (<span id="bookingHistoryDate"></span>)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="bookingHistoryModalBody" class="modal-body">
                <table id="bookingHistoryTable" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Divisi</th>
                            <th>Jam mulai</th>
                            <th>Jam selesai</th>
                            <th>Kegiatan</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button id="btn-history-add-booking" class="btn btn-primary text-center" date="" style="display: none;">
                    <img width="28" src="/images/google.webp">
                    <span>Tambah Peminjaman</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk Login -->
<div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="form-login" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginModalLabel">Login</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <input type="text" id="login-nis" class="form-control" placeholder="NIS" required/>
                </div>
                <div class="form-group">
                    <input type="password" id="login-password" class="form-control" placeholder="Password" required/>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal untuk menambah peminjaman -->
<div class="modal fade" id="bookingModal" tabindex="-1" role="dialog" aria-labelledby="bookingModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="form-booking" class="modal-content" action="{{ route('bookings.store') }}" method="POST">
            @csrf
            <input type="hidden" name="date" value="{{ session('google_bookings_date') ?? '' }}">
            <input type="hidden" name="nama">
            <input type="hidden" name="email">
            <input type="hidden" name="room_id" value="{{ session('google_bookings_room_id') ?? '' }}">
            <input type="hidden" name="department_id" value="{{ $user_department->id ?? '' }}">
            <div class="modal-header">
                <h5 class="modal-title" id="bookingModalLabel">Tambah Peminjaman</h5>
                <button id="btn-booking-form-close" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="date">Tanggal</label>
                    <input id="form-booking-date" class="form-control" value="{{ session('google_bookings_date') ?? '' }}" readonly/>
                </div>
                <div class="form-group">
                    <label for="department">Department</label>
                    <input id="booking-user-department" class="form-control" value="{{ $user_department->name ?? '' }}" readonly/>
                </div>
                <div class="row form-group">
                    <div class="col">
                        <label for="start_time">Jam Mulai</label>
                        <input class="timepicker form-control" name="start_time" placeholder="Jam Mulai" />
                    </div>
                    <div class="col">
                        <label for="end_time">Jam Selesai</label>
                        <input class="timepicker form-control" name="end_time" placeholder="Jam Selesai" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="users[]">Peserta</label>
                    <select name="users[]" id="select-users" multiple class="form-control" style="width: 100%">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="description" class="font-weight-bold">Description</label>
                    <textarea class="form-control" name="description" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button class="btn btn-primary">Tambah Peminjaman</button>
            </div>
        </form>
    </div>
</div>
@endsection
