@extends('layouts.app')

@section('head')
<link rel="stylesheet" href="/css/admin/dashboard.css">
<script>
    const userGetUrl = "{{ route('user.get') }}";
    const userStoreUrl = "{{ route('user.store') }}";
    const userUpdateUrl = "{{ route('user.update') }}";
</script>
<script src="/js/admin/dashboard.js"></script>
@endsection

@section('content')
<div class="container">
    <button class="btn btn-primary" id="btn-add-user" data-bs-toggle="modal" data-bs-target="#userModal">Tambah User</button>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>No.</th>
                <th>Name</th>
                <th>Email</th>
                <th>NIS</th>
                <th>Department</th>
                <th>Jabatan</th>
                <th>Role</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td>{{ $loop->index + 1 }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->nis }}</td>
                <td>{{ $user->department->name }}</td>
                <td>{{ $user->jabatan->name ?? 'Tidak ada' }}</td>
                <td>{{ ucfirst($user->role) }}</td>
                <td>
                    <button class="btn btn-warning btn-edit-user" id="{{ $user->id }}">Edit</button>
                    <a href="{{ route('user.destroy', $user->id) }}"><button class="btn btn-danger">Hapus</button></a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="form-user" class="modal-content" method="POST" action="{{ route('user.store') }}">
            @csrf
            <input type="hidden" name="id">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">Tambah User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="Name" name="name" required/>
                </div>
                <div class="form-group">
                    <input type="email" class="form-control" placeholder="Email" name="email" required/>
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="NIS" name="nis" required/>
                </div>
                <div class="form-group">
                    <input type="password" class="form-control" placeholder="Password" name="password" required/>
                </div>
                <div class="form-group">
                    <select class="form-control" name="department_id" required>
                        <option>Pilih Department</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <select class="form-control" name="jabatan_id" required>
                        <option>Pilih Jabatan</option>
                        @foreach ($jabatans as $jabatan)
                            <option value="{{ $jabatan->id }}">{{ $jabatan->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>
</div>
@endsection