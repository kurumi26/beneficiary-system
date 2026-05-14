<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Beneficiary Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 12px; }
        .report-header { margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid #cbd5e1; }
        .report-brand { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .report-brand td { border: 0; padding: 0; vertical-align: middle; }
        .report-brand__logos { width: 220px; }
        .report-brand__seal { width: 44px; height: 44px; vertical-align: middle; }
        .report-brand__wordmark { width: 148px; height: auto; margin-left: 8px; vertical-align: middle; }
        h1 { margin-bottom: 6px; }
        p { margin-top: 0; color: #475569; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th, td { border: 1px solid #cbd5e1; padding: 8px; text-align: left; }
        th { background: #e2e8f0; }
    </style>
</head>
<body>
    <div class="report-header">
        <table class="report-brand">
            <tr>
                <td class="report-brand__logos">
                    <x-application-logo class="report-brand__seal" />
                    <x-system-wordmark class="report-brand__wordmark" />
                </td>
                <td>
                    <h1>Beneficiary Records Report</h1>
                    <p>Generated on {{ now()->format('M d, Y h:i A') }}</p>
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID Number</th>
                <th>Full Name</th>
                <th>Barangay</th>
                <th>Status</th>
                <th>Category</th>
                <th>Date Issued</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($beneficiaries as $beneficiary)
                <tr>
                    <td>{{ $beneficiary->beneficiary_number }}</td>
                    <td>{{ $beneficiary->full_name }}</td>
                    <td>{{ $beneficiary->barangay }}</td>
                    <td>{{ ucfirst($beneficiary->status) }}</td>
                    <td>{{ $beneficiary->category }}</td>
                    <td>{{ optional($beneficiary->date_issued)->format('M d, Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
