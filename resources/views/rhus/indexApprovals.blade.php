@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center mt-1">
            <div class="col-md-12">
                @if (session('success'))
                    <div class="alert alert-success" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                <div>
                    <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                        <h3 class="fw-bold">BHU Applications</h3>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <table class="table table-borderless">
                                <thead>
                                    <tr>
                                        <th scope="col">Registration Id</th>
                                        <th scope="col">Health Center Name</th>
                                        <th scope="col">Barangay</th>
                                        <th scope="col">City</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($barangayHealthUnits as $unit)
                                        <tr>
                                            <th scope="row">{{ $unit['id'] ?? '' }}</th>
                                            <td>{{ $unit['healthCenterName'] ?? 'No name provided' }}</td>
                                            <td>{{ $unit['barangay'] ?? 'No barangay provided' }}</td>
                                            <td>{{ $unit['city'] ?? 'No city provided' }}</td>
                                            <td>
                                                <a href="{{ route('BHUs.edit', $unit['id']) }}"
                                                    class="btn btn-primary btn-sm">Review</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No pending applications.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
