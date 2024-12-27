<?php

namespace Condoedge\Eft\Kompo\Admin;

use App\Kompo\Common\Modal;
use App\Models\Eft\EftFile;

class AdminEftFileGenerateForm extends Modal
{
	protected $_Title = 'eft-create-file';

	public $model = EftFile::class;

	public function handle()
	{
		$this->model->run_date = request('file_date');
        $this->model->test_file = request('test_file');

		$this->setEftConfig();

        $this->model->finishSettingUpEft();
	}

	protected function setEftConfig()
	{
		$this->model->credit_or_debit = EftFile::EFT_CREDIT;

		$this->model->user_no = config('eft.user_no');
		$this->model->user_shortname = config('eft.user_shortname');
		$this->model->user_longname = config('eft.user_longname');

		$this->model->bank_code = config('eft.bank_code');

		$this->model->return_institution = config('eft.return_institution');
		$this->model->return_transit = config('eft.return_transit');
		$this->model->return_accountno = config('eft.return_accountno');
		
        $this->model->file_creation_no = $this->model->test_file ? '0000' : sprintf("%04d", $this->model->getMaxFileCreationNo() + 1);
	}

	public function body()
	{
		return _Rows(
			$this->getDateInput(),
			_Toggle('translate.test-file-question')->name('test_file'),
			_SubmitButton('translate.generate-file'),
		);
	}

	protected function getDateInput()
	{
		return _Date('translate.file-date')->name('file_date')->default(date('Y-m-d'));
	}

	public function rules()
	{
		return [
			'file_date' => 'required',
		];
	}
}
