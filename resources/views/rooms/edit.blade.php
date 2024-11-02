@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Card untuk form edit -->
            <div class="card">
                <div class="card-header">
                    <h4>Edit Ruangan</h4>
                </div>
                <div class="card-body">
                    <!-- Tampilkan pesan error jika ada -->
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Form untuk update room -->
                    <form action="{{ route('rooms.update', $room->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Input Nama Ruangan -->
                        <div class="form-group mb-3">
                            <label for="name" class="form-label">Nama Ruangan</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ $room->name }}" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="name" class="form-label">Gambar</label>
                            <input type="file" name="image" id="image" class="form-control">
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="name" class="form-label">Deskripsi</label>
                            <textarea name="description" id="description" class="form-control" required>{{ $room->description }}</textarea>
                        </div>

                        <!-- Tombol Submit dan Cancel -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('rooms.index') }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">Edit Ruangan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
