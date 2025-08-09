@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-3 mt-5">
            <h3 class="mb-0">Notifications</h3>
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

        <div class="card">
            <div class="card-body p-0">
                @if (empty($notifications))
                    <div class="text-center text-muted py-5">No notifications yet.</div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach ($notifications as $n)
                            @php
                                $title = $n['title'] ?? 'Notification';
                                $barangay = $n['barangayName'] ?? ($n['barangay_name'] ?? null);
                                $type = $n['type'] ?? '';
                                $created = $n['created_at'] ?? ($n['createdAt'] ?? '');
                                $human = $n['createdAtHuman'] ?? '';
                                $isRead = !empty($n['isRead']);

                                $desc = $n['message'] ?? null;
                                if (!$desc) {
                                    if ($type === 'barangay_registration') {
                                        $desc =
                                            ($barangay ?: 'A Barangay Health Unit') .
                                            ' submitted a registration request.';
                                    } elseif ($type === 'report_submitted') {
                                        $desc = 'A new health report was submitted by ' . ($barangay ?: 'a BHU') . '.';
                                    } else {
                                        $desc = 'You have a new notification.';
                                    }
                                }
                            @endphp

                            <div class="list-group-item">
                                <div class="d-flex">
                                    <div class="me-3 d-flex align-items-start">
                                        <span class="rounded-circle d-inline-block mt-1"
                                            style="width:10px;height:10px;background-color:{{ $isRead ? '#ced4da' : '#0b6ffd' }};"></span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="fw-semibold">{{ $title }}</div>
                                            <div class="text-end">
                                                <div class="small text-muted">{{ $created ?: '—' }}</div>
                                                @if ($human)
                                                    <div class="small text-muted">{{ $human }}</div>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="text-muted">{{ $desc }}</div>

                                        <div class="d-flex align-items-center gap-3 mt-2">
                                            @if ($barangay)
                                                <span class="badge bg-light text-dark">Barangay: {{ $barangay }}</span>
                                            @endif
                                            @if (!$isRead)
                                                <form action="{{ route('rhu.notifications.read', $n['id']) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-sm btn-outline-secondary">Mark as read</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
