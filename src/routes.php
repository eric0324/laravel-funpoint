<?php
use TsaiYiHua\FunPoint\Http\Controllers\FunPointController;

Route::prefix('ecpay')->group(function(){
    Route::post('notify', [FunPointController::class, 'notifyUrl'])->name('ecpay.notify');
    Route::post('return',  [FunPointController::class, 'returnUrl'])->name('ecpay.return');
});
