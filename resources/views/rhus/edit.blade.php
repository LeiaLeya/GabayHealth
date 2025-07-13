{{-- filepath: resources/views/RuralHealthUnit/edit.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <h2 class="mb-4">Edit Rural Health Unit</h2>
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <img src="{{ asset('images/Doctor.png') }}" class="card-img-top" alt="RHU Image">
                    <div class="card-body">
                        <h5 class="card-title">{{ $ruralHealthUnit['name'] ?? 'No Name' }}</h5>
                        <p class="card-text mb-1"><strong>Address:</strong> {{ $ruralHealthUnit['fullAddress'] ?? 'N/A' }}
                        </p>
                        <p class="card-text"><strong>City:</strong> {{ $cityName ?? 'N/A' }}</p>
                        {{-- Approve Button --}}
                        @if (($ruralHealthUnit['status'] ?? '') !== 'approved')
                            <form method="POST" action="{{ route('RHUs.update', $ruralHealthUnit['id']) }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="approved">
                                <button type="submit" class="btn btn-success mt-3">Approve</button>
                            </form>
                        @else
                            <div class="alert alert-success mt-3">This RHU is already approved.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
