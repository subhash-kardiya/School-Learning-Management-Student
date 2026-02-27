@extends('layouts.admin')

@section('title', 'Edit Room')

@section('content')
    <div class="container-fluid py-4 room-module-compact">
        <div class="mb-4">
            <a href="{{ route('rooms.index') }}" class="text-muted text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i> Back to Rooms
            </a>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Edit Room</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('rooms.update', $room->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Room Number</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $room->name) }}" placeholder="e.g. 101">
                            @error('name')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Capacity</label>
                            <input type="number" name="capacity" class="form-control @error('capacity') is-invalid @enderror"
                                value="{{ old('capacity', $room->capacity) }}" min="1" placeholder="e.g. 40">
                            @error('capacity')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="1" {{ (string) old('status', (string) $room->status) === '1' ? 'selected' : '' }}>
                                    Active
                                </option>
                                <option value="0" {{ (string) old('status', (string) $room->status) === '0' ? 'selected' : '' }}>
                                    Inactive
                                </option>
                            </select>
                            @error('status')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary-fancy px-5">Update Room</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('css/resize/room-compact.css') }}">
@endpush
