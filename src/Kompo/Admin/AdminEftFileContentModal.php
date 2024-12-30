<?php

namespace Condoedge\Eft\Kompo\Admin;

use App\Kompo\Common\Modal;
use App\Models\Eft\EftFile;

class AdminEftFileContentModal extends Modal
{
	protected $_Title = 'EFT file content';
    public $class = 'overflow-y-auto mini-scroll';
    public $style = 'max-height: 95vh';

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
