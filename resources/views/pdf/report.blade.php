<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #212529; }
        .header { background: #17365d; color: #fff; padding: 10px 14px; }
        .header h1 { margin: 0; font-size: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #17365d; color: #fff; padding: 5px; text-align: left; }
        td { padding: 5px; border-bottom: 1px solid #dee2e6; }
        tr:nth-child(even) td { background: #f5f7fb; }
    </style>
</head>
<body>
    <div class="header"><h1>Robert Hotel &mdash; {{ $title }}</h1><div>Generated {{ now()->format('M d, Y h:i A') }} &middot; {{ $rows->count() }} record(s)</div></div>
    <table>
        <tr>@foreach ($headings as $heading)<th>{{ $heading }}</th>@endforeach</tr>
        @foreach ($rows as $row)
            <tr>@foreach ($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>
        @endforeach
    </table>
</body>
</html>
