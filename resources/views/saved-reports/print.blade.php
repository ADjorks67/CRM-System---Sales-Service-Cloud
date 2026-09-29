<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $savedReport->name }} — Print</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 1.5rem; color: #111; }
        h1 { font-size: 1.25rem; margin-bottom: 0.25rem; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 1rem; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f5f5f5; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <p class="no-print"><button type="button" onclick="window.print()">Print / Save as PDF</button></p>
    <h1>{{ $savedReport->name }}</h1>
    <p>{{ $result['rows']->count() }} row(s) · FR-RPT-005 browser print stub</p>
    <table>
        <thead>
            <tr>
                @foreach ($result['column_labels'] as $label)
                    <th>{{ $label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($result['rows'] as $row)
                <tr>
                    @foreach ($result['columns'] as $column)
                        <td>{{ $row[$column] ?? '—' }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
