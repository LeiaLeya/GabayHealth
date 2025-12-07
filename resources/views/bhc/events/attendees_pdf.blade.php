<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance - {{ $event['title'] ?? 'Event' }}</title>
    <style>
        @page { margin: 36px; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 20px; margin: 0 0 6px 0; }
        .meta { margin-bottom: 14px; }
        .meta div { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 6px 8px; }
        th { background: #f2f2f2; text-align: left; }
        .sign-col { width: 35%; }
        .small { color: #666; }
    </style>
</head>
<body>
    <h1>Attendance Sheet</h1>
    <div class="meta">
        <div><strong>Event:</strong> {{ $event['title'] ?? '' }}</div>
        <div><strong>Date:</strong> {{ isset($event['date']) ? \Carbon\Carbon::parse($event['date'])->format('F d, Y') : '' }}</div>
        <div><strong>Time:</strong>
            @if(isset($event['start_time']) && isset($event['end_time']))
                {{ \Carbon\Carbon::parse($event['start_time'])->format('h:i A') }} - {{ \Carbon\Carbon::parse($event['end_time'])->format('h:i A') }}
            @elseif(isset($event['time']))
                {{ $event['time'] }}
            @else
                N/A
            @endif
        </div>
        <div><strong>Venue:</strong> {{ $event['location'] ?? '' }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:4%">#</th>
                <th style="width:36%">Name</th>
                <th style="width:10%">Age</th>
                <th style="width:15%">Gender</th>
                <th class="sign-col">Signature</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendees as $index => $attendee)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $attendee['name'] }}</td>
                    <td>{{ $attendee['age'] ?? '' }}</td>
                    <td>{{ $attendee['gender'] ?? '' }}</td>
                    <td></td>
                </tr>
            @empty
                @for($i = 1; $i <= 20; $i++)
                    <tr>
                        <td>{{ $i }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endfor
            @endforelse
        </tbody>
    </table>

    <p class="small" style="margin-top: 10px;">Please sign above to confirm your attendance.</p>
</body>
</html>

