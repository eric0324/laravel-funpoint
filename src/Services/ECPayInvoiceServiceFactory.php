<?php


namespace TsaiYiHua\FunPoint\Services;


use Illuminate\Support\Facades\App;

class FunPointInvoiceServiceFactory
{
    static public function create($contract, $method = null)
    {
        /** bind service */
        $targetClass = __NAMESPACE__.'\\Invoice\\FunPoint_'.$method;
        App::bind($contract, $targetClass);
    }
}
