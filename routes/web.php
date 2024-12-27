<?php

Route::layout('layouts.dashboard')->middleware(['auth', 'role:admin'])->group(function(){

    //Call them in own project
    //Route::get('download-eft-file/{id}', [Condoedge\Eft\Http\Controllers\EftController::class, 'downloadEftFile'])->name('eft-file.download');
    //Route::get('admin/eft-files', Condoedge\Eft\Kompo\Admin\AdminEftFilesTable::class)->name('admin.eft-files');

});

