<?php

namespace Condoedge\Eft\Kompo\Admin;

use App\Kompo\Common\Modal;
use App\Models\Eft\EftFile;

class AdminEftFileGenerateForm extends Modal
{
	protected $_Title = 'finance.generate-eft-file';

	public function created()
	{

	}

	public function handle()
	{
		EftFile::createEftFile(request('file_date'), request('test_file'));

	}

	public function body()
	{
		return _Rows(
			_Date('finance.file-date')->name('file_date')->default(date('Y-m-d')),
			_Toggle('finance.test-file-question')->name('test_file'),
			_SubmitButton('finance.generate-file-with-marked-transfers'),
		);
	}

	public function rules()
	{
		return [
			'file_date' => 'required',
		];
	}
}
