@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-12">
                @if (session('success'))
                    <div class="alert alert-success" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                <div>
                    <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
                        <h3>Rural Health Unit Applications</h3>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <table class="table table-borderless">
                                @forelse($ruralHealthUnits as $unit)
                                    <thead>
                                        <tr>
                                            <th scope="col">Registration Id</th>
                                            <th scope="col">Rural Health Unit</th>
                                            <th scope="col"></th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <tr>
                                            <th scope="row">{{ $unit['id'] ?? '' }}</th>
                                            <td>{{ $unit['name'] ?? '' }}</td>
                                            <td>
                                                <a href="{{ route('RHUs.edit', $unit['id']) }}"
                                                    class="btn btn-primary btn-sm">Review</a>
                                            </td>
                                        </tr>
                                    </tbody>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center"><br>No pending applications.</td>
                                    </tr>
                                @endforelse
                            </table>
                            {{-- {{ $ruralHealthUnits->links() }} --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
