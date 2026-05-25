<?php

namespace Condoedge\Eft\Exports;

use Condoedge\Eft\Models\EftFile;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EftFileLinesExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(protected EftFile $eftFile)
    {
    }

    public function collection()
    {
        return $this->eftFile->eftLines()
            ->whereNotNull('line_amount')
            ->get();
    }

    public function headings(): array
    {
        return [
            __('eft-counterparty'),
            __('eft-amount'),
            __('eft-caused-error?'),
            __('eft-error-reason'),
        ];
    }

    public function map($line): array
    {
        return [
            $this->counterpartyLabel($line),
            (float) $line->line_amount,
            $line->caused_error ? __('generic.yes') : __('generic.no'),
            $line->error_reason,
        ];
    }

    protected function counterpartyLabel($line): string
    {
        return $line->line_display ?? '—';
    }
}
