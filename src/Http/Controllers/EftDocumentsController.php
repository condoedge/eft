<?php

namespace Condoedge\Eft\Http\Controllers;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Condoedge\Eft\Exports\EftFileLinesExport;
use App\Models\Eft\EftFile;
use Maatwebsite\Excel\Facades\Excel;

class EftDocumentsController extends Controller
{
    public function pdf($id)
    {
        $eftFile = $this->eftFileModel()::findOrFail($id);

        $pdf = Pdf::loadView($this->pdfView(), $this->pdfData($eftFile));

        return $pdf->stream($this->pdfFilename($eftFile));
    }

    public function excel($id)
    {
        $eftFile = $this->eftFileModel()::findOrFail($id);

        return Excel::download(
            $this->excelExport($eftFile),
            $this->excelFilename($eftFile),
        );
    }

    protected function eftFileModel(): string
    {
        return EftFile::class;
    }

    protected function pdfView(): string
    {
        return 'eft::audit-report';
    }

    protected function pdfData($eftFile): array
    {
        return ['eftFile' => $eftFile];
    }

    protected function pdfFilename($eftFile): string
    {
        return 'audit-eft-' . $eftFile->file_creation_no . '.pdf';
    }

    protected function excelExport($eftFile)
    {
        return new EftFileLinesExport($eftFile);
    }

    protected function excelFilename($eftFile): string
    {
        return 'eft-export-' . $eftFile->file_creation_no . '.xlsx';
    }
}
