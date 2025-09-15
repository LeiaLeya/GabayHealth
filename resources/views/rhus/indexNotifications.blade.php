@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-3 mt-5">
            <h3 class="mb-0 fw-bold">Notifications</h3>
            @php
                $unreadCount = 0;
                foreach ($notifications ?? [] as $n) {
                    if (empty($n['isRead'])) {
                        $unreadCount++;
                    }
                }
            @endphp
            @if (($notifications ?? null) && $unreadCount > 0)
                <span class="badge bg-primary">{{ $unreadCount }} new</span>
            @endif
        </div>

        {{-- <div class="card"> --}}
            <div class="card-body p-0">
                @if (empty($notifications))
                    <div class="text-center text-muted py-5">No notifications yet.</div>
                @else
                    <div class="d-flex flex-column gap-3 px-2 px-md-4 py-2">
                        @foreach ($notifications as $n)
                            @php
                                $name = $n['senderName'] ?? ($n['title'] ?? 'Unknown');
                                $activity = $n['message'] ?? '';
                                $type = $n['type'] ?? '';
                                $project = $n['project'] ?? ($n['barangayName'] ?? ($n['barangay_name'] ?? null));
                                $created = $n['created_at'] ?? ($n['createdAt'] ?? '');
                                $human = $n['createdAtHuman'] ?? '';
                                $isRead = !empty($n['isRead']);
                                $timestamp =
                                    $human ?: ($created ? \Carbon\Carbon::parse($created)->diffForHumans() : '');
                                $desc = $activity;
                                if (!$desc) {
                                    if ($type === 'barangay_registration') {
                                        $desc =
                                            ($project ?: 'A Barangay Health Unit') .
                                            ' submitted a registration request.';
                                    } elseif ($type === 'report_submitted') {
                                        $desc = 'A new health report was submitted by ' . ($project ?: 'a BHU') . '.';
                                    } else {
                                        $desc = 'You have a new notification.';
                                    }
                                }
                            @endphp
                            <div>
                                <div class="card shadow-sm border-0 h-100"
                                    style="background:{{ $isRead ? '#fff' : '#f7fafd' }}; border-radius: 16px;">
                                    <div class="card-body d-flex align-items-center py-3">
                                        <img src="{{ asset('images/RHU.png') }}" alt="avatar" class="rounded-circle me-3"
                                            style="width:40px;height:40px;object-fit:cover;">
                                        <div class="flex-grow-1">
                                            <div class="mb-1">
                                                <span class="fw-bold" style="color:#222;">{{ $name }}</span>

                                            </div>
                                            <div class="mb-1">
                                                <span
                                                    style="color:#222;font-size:1.08em;font-weight:500;">{{ $desc }}</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                @if ($timestamp)
                                                    <span class="text-muted">{{ $project }}</span>
                                                    <span class="mx-1" style="font-size:1.2em; color:#bbb;">&bull;</span>
                                                    <span class="text-muted small">{{ $timestamp }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        {{-- </div> --}}
    </div>
@endsection
