@extends('layouts.app')

@section('content')
    <div class="row justify-content-center mt-1">
        <div class="col-md-12">
            @if (session('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            <div>
                <div class="card-header d-flex justify-content-between align-items-center mb-4 mt-5">
                    <h3>Rural Health Unit Applications</h3>

                </div>

                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th scope="col">Registration Id</th>
                                <th scope="col">Rural Health Unit</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ruralHealthUnits as $unit)
                                <tr>
                                    <th scope="row">{{ $unit->id }}</th>
                                    <td>{{ $unit->name }}</td>
                                    <td>
                                        <a href="{{ route('RHUs.edit', $unit->id) }}"
                                            class="btn btn-primary btn-sm">Review</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">No pending applications.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{ $ruralHealthUnits->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
