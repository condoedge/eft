<?php

namespace Condoedge\Eft\Kompo\Admin;

use App\Kompo\Common\Modal;
use App\Models\Eft\EftFile;

class AdminEftCompletionModal extends Modal
{
	protected $_Title = 'EFT pick completion';

	public $model = EftFile::class;
	
	public function body()
	{
		return _Rows(
			_Date('Bank transaction date')->name('completed_date'),
			_InputNumber('Completed amount confirmation')->name('completed_amount'),
			_FlexBetween(
				_Button('Completed fully')->submit('markCompletedFully'),
				_Button('Completed with rejections')->outlined()->submit('markCompletedWithRejections'),
			)->class('space-x-4'),
		);
	}

	public function markCompletedFully()
	{
		$this->model->markCompletedFully(request('completed_date'), request('completed_amount'));
	}

	public function markCompletedWithRejections()
	{
		$this->model->markCompletedWithRejections(request('completed_date'), request('completed_amount'));		
	}
}
