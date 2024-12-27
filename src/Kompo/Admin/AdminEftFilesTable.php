<?php

namespace Condoedge\Eft\Kompo\Admin;

use App\Models\Eft\EftFile;
use Kompo\Table;

class AdminEftFilesTable extends Table
{
    public $containerClass = 'container-fluid';

    public function query()
    {
        return EftFile::orderByDesc('run_date')->with('eftLines');
    }

    public function top()
    {
        $monitorTable = $this->monitorTable();

        return _Rows(
            _FlexBetween(
                _Html('finance.eft-files')->pageTitle()->class('mb-4'),
                !$monitorTable ? null : 
                    _Link('Show transfers being loaded next')->toggleId('transfers-to-load-table'),
                _Button('finance.generate-file')->icon('icon-plus')->outlined()->class('mb-4')
                    ->selfCreate('getGenerateEftFileModal')->inModal()
            ),
            !$monitorTable ? null : 
                _Rows($monitorTable)->class('mb-4')->id('transfers-to-load-table')
        );
    }

    protected function monitorTable()
    {
        //To override in app
    }

    public function headers()
    {
        return [
            _Th('finance.eft-date'),
            _Th('finance.filename'),
            _Th('finance.number-transfers'),
            _Th('finance.download'),
            _Th('finance.deposited'),
            _Th(),
        ];
    }

    public function render($eftFile)
    {
    	return _TableRow(
            _Html($eftFile->run_date),
            _Html($eftFile->filename),
            _Html($eftFile->eftLines->count() - 2),
            _Link()->icon('download')->href('eft-file.download', ['id' => $eftFile->id])->inNewTab(),
            $eftFile->deposited_at ?
                _Html($eftFile->deposited_at)->icon('icon-check') :
                _Link('Confirm deposit')->selfPost('markDeposited', ['id' => $eftFile->id])->browse(),
            _Delete()->byKey($eftFile),
        )->selfGet('getEftFileContentModal', ['id' => $eftFile->id])->inModal();
    }

    public function getGenerateEftFileModal()
    {
        return new AdminEftFileGenerateForm();
    }

    public function getEftFileContentModal($id)
    {
        return new AdminEftFileContentModal($id);
    }

    public function markDeposited($id)
    {
        $eftFile = EftFile::findOrFail($id);
        $eftFile->markDeposited();
    }
}
