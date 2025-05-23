<?php

namespace Condoedge\Eft\Kompo\Admin;

use App\Models\Eft\EftFile;
use Kompo\Table;

class AdminEftFilesTable extends Table
{
    public $containerClass = 'container-fluid';
    public $id = 'admin-eft-files-table';

    public function query()
    {
        return EftFile::orderByDesc('file_creation_no')->with('eftLines');
    }

    public function top()
    {
        $monitorTable = $this->monitorTable();

        return _Rows(
            _FlexBetween(
                _Html('eft-eft-files')->pageTitle()->class('mb-4'),
                _Flex4(
                    !$monitorTable ? null : 
                        _Button('eft-display-next-transfers')->outlined()->toggleId('transfers-to-load-table'),
                        _Button('eft-create-file')->icon(_sax('add',18))
                            ->selfCreate('getGenerateEftFileModal')->inModal()
                )->class('mb-4'),
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
            _Th('eft-debit-or-credit'),
            _Th('eft-file-creation-no'),
            _Th('eft-date'),
            _Th('eft-filename'),
            _Th('eft-number-transfers'),
            _Th('eft-amount'),
            _Th('eft-download'),
            _Th('eft-confirm-transaction'),
            _Th('eft-confirm-acceptance'),
            _Th('eft-confirm-completion'),
            _Th(),
        ];
    }

    public function render($eftFile)
    {
    	return _TableRow(
            _Html($eftFile->credit_or_debit),
            _Html($eftFile->file_creation_no),
            _Html($eftFile->run_date),
            _Html($eftFile->filename),
            _Html($eftFile->eftLines->count() - 2),
            _Currency($eftFile->eftLines->sum('line_amount')),
            _Link()->icon('download')->href('eft-file.download', ['id' => $eftFile->id])->inNewTab(),
            $eftFile->deposited_at ?
                _Html($eftFile->deposited_at->format('Y-m-d H:i')) :
                _Button('?')->selfPost('markDeposited', ['id' => $eftFile->id])->browse(),
            $eftFile->accepted_at ? _Html($eftFile->accepted_at->format('Y-m-d H:i')) : (
                $eftFile->rejected_at ? _Html($eftFile->rejected_at->format('Y-m-d H:i'))->icon('icon-times') : _Flex2(
                    _Button()->icon('icon-check')->selfUpdate('getAcceptationModal', ['id' => $eftFile->id])->inModal(),
                    _Button()->icon('icon-times')->selfPost('markRejected', ['id' => $eftFile->id])->browse(),
                )
            ),
            $eftFile->completed_at ? _Rows(
                    _Html($eftFile->completed_at->format('Y-m-d H:i')),
                    _Currency($eftFile->completed_amount)->class('text-sm font-bold'),
                ) : 
                _Button('eft-complete?')->selfUpdate('getCompletionModal', ['id' => $eftFile->id])->inModal(),
            _Delete($eftFile),
        )->selfGet('getEftFileContentModal', ['id' => $eftFile->id])->inModal();
    }

    public function getGenerateEftFileModal()
    {
        return new AdminEftFileGenerateForm();
    }

    public function getEftFileContentModal($id)
    {
        return new AdminEftFileContentTable([
            'eft_file_id' => $id,
        ]);
    }

    public function markDeposited($id)
    {
        $eftFile = EftFile::findOrFail($id);
        $eftFile->markDeposited();
    }

    public function getAcceptationModal($id)
    {
        return new AdminEftAcceptedModal($id);
    }

    public function markRejected($id)
    {
        $eftFile = EftFile::findOrFail($id);
        $eftFile->markRejected();
    }

    public function getCompletionModal($id)
    {
        return new AdminEftFileContentTable([
            'eft_file_id' => $id,
        ]);
    }
}
