<?php

namespace TsaiYiHua\FunPoint\Libs;


use Illuminate\Support\Facades\App;
use TsaiYiHua\FunPoint\Constants\FunPointEncryptType;
use TsaiYiHua\FunPoint\Exceptions\FunPointException;
use TsaiYiHua\FunPoint\Services\FunPointInvoiceServiceFactory;
use TsaiYiHua\FunPoint\Services\FunPointService;

class FunPoint_Invoice_Send
{
    // 發票物件
    /** @var FunPointService */
    public static $InvoiceObj ;
    /** @var FunPointService */
    public static $InvoiceObj_Return ;

    /**
     * 背景送出資料
     */
    static function CheckOut(
        $arParameters = array(),
        $HashKey = '',
        $HashIV = '',
        $Invoice_Method = '',
        $ServiceURL = ''
    )
    {

        // 發送資訊處理
        $arParameters = self::process_send($arParameters, $HashKey, $HashIV, $Invoice_Method, $ServiceURL);

        $szResult = FunPoint_IO::ServerPost($arParameters, $ServiceURL);

        // 回傳資訊處理
        $arParameters_Return = self::process_return($szResult, $HashKey, $HashIV, $Invoice_Method);

        return $arParameters_Return ;
    }

    // 資料檢查與過濾(送出)
    protected static function process_send(
        $arParameters = array(),
        $HashKey = '',
        $HashIV = '',
        $Invoice_Method = '',
        $ServiceURL = ''
    )
    {

        //宣告物件
        FunPointInvoiceServiceFactory::create(FunPointService::class, $Invoice_Method);
        self::$InvoiceObj = App::make(FunPointService::class);

        // 1寫入參數
        $arParameters = self::$InvoiceObj->insertString($arParameters);

        // 2檢查共用參數
        self::check_string($arParameters['MerchantID'], $HashKey, $HashIV, $Invoice_Method, $ServiceURL);

        // 3檢查各別參數
        $arParameters = self::$InvoiceObj->checkExtendString($arParameters);

        // 4處理需要轉換為urlencode的參數
        $arParameters = self::urlencode_process($arParameters, self::$InvoiceObj->urlencode_field);

        // 5欄位例外處理方式(送壓碼前)
        $arException = self::$InvoiceObj->checkException($arParameters);

        // 6產生壓碼
        $arParameters['CheckMacValue'] = self::generate_checkmacvalue(
            $arException,
            self::$InvoiceObj->none_verification, $HashKey, $HashIV
        );

        return $arParameters ;
    }

    /**
     * 資料檢查與過濾(回傳)
     */
    static function process_return($sParameters = '', $HashKey = '', $HashIV = '', $Invoice_Method = '')
    {
        FunPointInvoiceServiceFactory::create(FunPointService::class, $Invoice_Method);
        self::$InvoiceObj_Return = App::make(FunPointService::class);

        // 7字串轉陣列
        $arParameters = self::string_to_array($sParameters);

        // 8欄位例外處理方式(送壓碼前)
        $arException = self::$InvoiceObj_Return->checkException($arParameters);

        // 9產生壓碼(壓碼檢查)
        if(isset($arParameters['CheckMacValue'])){
            $CheckMacValue = self::generate_checkmacvalue(
                $arException,
                self::$InvoiceObj_Return->none_verification, $HashKey, $HashIV
            );

            if($CheckMacValue != $arParameters['CheckMacValue']){
                throw new FunPointException('注意：壓碼錯誤');
            }
        }

        // 10處理需要urldecode的參數
        $arParameters = self::urldecode_process($arParameters, self::$InvoiceObj_Return->urlencode_field);

        return $arParameters ;
    }

    /**
     * 2檢查共同參數
     */
    protected static function check_string(
        $MerchantID = '',
        $HashKey = '',
        $HashIV = '',
        $Invoice_Method = 'INVOICE',
        $ServiceURL = ''
    )
    {

        $arErrors = array();

        // 檢查是否傳入動作方式
        if($Invoice_Method == '' || $Invoice_Method == 'Invoice_Method') {
            array_push($arErrors, 'Invoice_Method is required.');
        }

        // 檢查是否有傳入MerchantID
        if(strlen($MerchantID) == 0) {
            array_push($arErrors, 'MerchantID is required.');
        }

        if(strlen($MerchantID) > 10) {
            array_push($arErrors, 'MerchantID max langth as 10.');
        }

        // 檢查是否有傳入HashKey
        if(strlen($HashKey) == 0) {
            array_push($arErrors, 'HashKey is required.');
        }

        // 檢查是否有傳入HashIV
        if(strlen($HashIV) == 0) {
            array_push($arErrors, 'HashIV is required.');
        }

        // 檢查是否有傳送網址
        if(strlen($ServiceURL) == 0) {
            array_push($arErrors, 'Invoice_Url is required.');
        }

        if(sizeof($arErrors)>0) throw new FunPointException(join('<br>', $arErrors));
    }

    /**
     * 4處理需要轉換為urlencode的參數
     */
    protected static function urlencode_process($arParameters = array(), $urlencode_field = array())
    {
        foreach($arParameters as $key => $value) {

            if(isset($urlencode_field[$key])) {
                $arParameters[$key] = urlencode($value);
                $arParameters[$key] = FunPoint_Invoice_CheckMacValue::Replace_Symbol($arParameters[$key]);
            }
        }

        return $arParameters ;
    }

    /**
     * 6,9產生壓碼
     */
    protected static function generate_checkmacvalue(
        $arParameters = array(),
        $none_verification = array(),
        $HashKey = '',
        $HashIV = ''
    )
    {

        $sCheck_MacValue = '';

        // 過濾不需要壓碼的參數
        foreach($none_verification as $key => $value) {
            if(isset($arParameters[$key])) {
                unset($arParameters[$key]) ;
            }
        }

        $sCheck_MacValue = FunPoint_Invoice_CheckMacValue::generate(
            $arParameters,
            $HashKey,
            $HashIV,
            FunPointEncryptType::ENC_MD5
        );

        return $sCheck_MacValue ;
    }

    /**
     * 7 字串轉陣列
     */
    protected static function string_to_array($Parameters = '')
    {

        $aParameters 	 = array();
        $aParameters_Tmp = array();

        $aParameters_Tmp  = explode('&', $Parameters);

        foreach($aParameters_Tmp as $part) {
            list($paramName, $paramValue) = explode('=', $part, 2);
            $aParameters[$paramName] = $paramValue ;
        }

        return $aParameters ;
    }

    /**
     * 10處理urldecode的參數
     */
    protected static function urldecode_process($arParameters = array(), $urlencode_field = array())
    {
        foreach($arParameters as $key => $value) {
            if(isset($urlencode_field[$key])) {
                $arParameters[$key] = FunPoint_Invoice_CheckMacValue::Replace_Symbol_Decode($arParameters[$key]);
                $arParameters[$key] = urldecode($value);
            }
        }

        return $arParameters ;
    }
}
