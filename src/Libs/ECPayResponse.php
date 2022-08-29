<?php

namespace TsaiYiHua\FunPoint\Libs;


use Illuminate\Support\Facades\App;
use MirrorFiction\Payment\Services\Donate\DonateService;
use TsaiYiHua\FunPoint\Exceptions\FunPointException;
use TsaiYiHua\FunPoint\Services\FunPointInvoiceServiceFactory;
use TsaiYiHua\FunPoint\Services\FunPointService;

class FunPointResponse
{
    // 發票物件
    /** @var DonateService */
    public static $objReturn ;

    /**
     * 取得 Response 資料
     *
     * @param  array $merchantInfo
     * @param  array $parameters
     * @return array
     */
    static function response($merchantInfo = [], $parameters = [])
    {
        FunPointInvoiceServiceFactory::create(FunPointService::class, $merchantInfo['method']);
        self::$objReturn = App::make(FunPointService::class);

        // 壓碼檢查
        $parametersTmp = $parameters ;
        unset($parametersTmp['CheckMacValue']);
        $checkMacValue = FunPoint_Invoice_CheckMacValue::generate(
            $parametersTmp,
            $merchantInfo['hashKey'],
            $merchantInfo['hashIv']
        );

        if($checkMacValue != $parameters['CheckMacValue']){
            throw new FunPointException('注意：壓碼錯誤');
        }

        return $parameters ;
    }
}
