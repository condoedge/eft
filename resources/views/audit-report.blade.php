<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8">
  <title>{{ __('eft-audit-title') }} #{{ $eftFile->file_creation_no }}</title>
  <style>
    @page { size: A4; margin: 18mm 16mm; }
    body { font-family: 'Helvetica Neue', Arial, sans-serif; color: #1f2937; font-size: 11pt; line-height: 1.4; margin: 0; }
    h1 { font-size: 16pt; margin: 0 0 4px; }
    h2 { font-size: 12pt; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; margin: 18px 0 10px; }
    table { width: 100%; border-collapse: collapse; font-size: 10pt; }
    th { background: #f3f4f6; text-align: left; padding: 6px 8px; border-bottom: 1px solid #d1d5db; font-weight: 600; }
    td { padding: 6px 8px; border-bottom: 1px solid #f3f4f6; }
    .right { text-align: right; }
    .mono { font-family: 'Courier New', monospace; font-size: 9pt; }
    .header { display: flex; justify-content: space-between; border-bottom: 2px solid #1f2937; padding-bottom: 12px; margin-bottom: 18px; }
    .meta { font-size: 9pt; color: #6b7280; text-align: right; line-height: 1.6; }
    .header-dates { display: flex; gap: 14px; margin: 6px 0 22px; padding: 10px 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; }
    .date-block { flex: 1; font-size: 10pt; }
    .date-block span { display: block; font-size: 8pt; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
    .totals-row td { border-top: 2px solid #1f2937; padding-top: 8px; padding-bottom: 8px; background: #f3f4f6; }
    .error { color: #991b1b; }
  </style>
</head>
<body>

  <div class="header">
    <div>
      <h1>{{ __('eft-audit-title') }}</h1>
      <div class="meta">{{ __('eft-audit-subtitle') }}</div>
    </div>
    <div class="meta">
      <div><strong>{{ __('eft-file-creation-no') }}</strong> {{ $eftFile->file_creation_no }}</div>
      <div><strong>{{ __('eft-filename') }}</strong> <span class="mono">{{ $eftFile->filename }}</span></div>
    </div>
  </div>

  <div class="header-dates">
    <div class="date-block">
      <span>{{ __('eft-date') }}</span>
      {{ $eftFile->run_date ?? '—' }}
    </div>
    <div class="date-block">
      <span>{{ __('eft-confirm-transaction') }}</span>
      {{ $eftFile->deposited_at?->format('Y-m-d') ?? '—' }}
    </div>
    <div class="date-block">
      <span>{{ __('eft-confirm-acceptance') }}</span>
      {{ $eftFile->accepted_at?->format('Y-m-d') ?? '—' }}
    </div>
    <div class="date-block">
      <span>{{ __('eft-confirm-completion') }}</span>
      {{ $eftFile->completed_at?->format('Y-m-d') ?? '—' }}
    </div>
  </div>

  <h2>{{ __('eft-eft-file-content') }}</h2>
  <table>
    <thead>
      <tr>
        <th>{{ __('eft-counterparty') }}</th>
        <th class="right">{{ __('eft-amount') }}</th>
        <th>{{ __('eft-caused-error?') }}</th>
      </tr>
    </thead>
    <tbody>
      @php $total = 0; @endphp
      @foreach($eftFile->eftLines()->whereNotNull('line_amount')->get() as $line)
        <tr>
          <td>{{ $line->line_display ?? '—' }}</td>
          <td class="right">{{ number_format((float) $line->line_amount, 2) }}</td>
          <td class="{{ $line->caused_error ? 'error' : '' }}">{{ $line->error_reason }}</td>
        </tr>
        @php $total += (float) $line->line_amount; @endphp
      @endforeach
      <tr class="totals-row">
        <td><strong>{{ __('eft-all-file') }}</strong></td>
        <td class="right"><strong>{{ number_format($total, 2) }}</strong></td>
        <td></td>
      </tr>
    </tbody>
  </table>

</body>
</html>
