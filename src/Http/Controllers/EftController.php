<?php

namespace Condoedge\Eft\Http\Controllers;

use App\Models\Eft\EftFile;

class EftController extends Controller
{
    public function downloadEftFile($id)
    {
        $eftFile = EftFile::findOrFail($id);

        return $this->downloadFile($eftFile->getEftFileContent(), $eftFile->filename);
    }

    public function downloadFile($txt, $fileName)
    {
        // use headers in order to generate the download
        $headers = [
          'Content-type' => 'text/csv', 
          'Content-Disposition' => sprintf('attachment; filename="%s"', $fileName),
          'Content-Length' => strlen($txt)
        ];

        // make a response, with the content, a 200 response code and the headers
        return response()->make($txt, 200, $headers);
    }
}
