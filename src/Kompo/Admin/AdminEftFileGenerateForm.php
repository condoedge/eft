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
        $this->model->createEftFileWithLines(request('file_date'), request('test_file'), request('file_creation_no'));
	}

	public function body()
	{
		return _Rows(
			$this->getDateInput(),
			_Toggle('eft-test-file-question')->name('test_file'),
			_Input('eft-file-creation-number')->name('file_creation_no')->default($this->model->getMaxFileCreationNo() + 1),
			_SubmitButton('eft-generate-file'),
		);
	}

	protected function getDateInput()
	{
		return _Date('eft-file-date')->name('file_date')->default(date('Y-m-d'));
	}

	public function rules()
	{
		return [
			'file_date' => 'required',
		];
	}
}
