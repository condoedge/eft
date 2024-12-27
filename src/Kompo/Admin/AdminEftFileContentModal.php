<?php

namespace Condoedge\Eft\Kompo\Admin;

use App\Kompo\Common\Modal;
use App\Models\Finance\EftFile;

class AdminEftFileContentModal extends Modal
{
	protected $_Title = 'EFT file content';

	public $model = EftFile::class;
	
	public function body()
	{
		return _Rows(
			new AdminEftFileContentTable([
				'eft_file_id' => $this->model->id,
			]),
		);
	}
}
