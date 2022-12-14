<?php
namespace TsaiYiHua\FunPoint\Services\Invoice;

use TsaiYiHua\FunPoint\Constants\FunPointInvoiceTagType;
use TsaiYiHua\FunPoint\Constants\FunPointNotifiedType;
use TsaiYiHua\FunPoint\Constants\FunPointNotifyType;
use TsaiYiHua\FunPoint\Exceptions\FunPointException;
use TsaiYiHua\FunPoint\Services\FunPointService;

/**
 *  J發送通知
 */
class FunPoint_INVOICE_NOTIFY implements FunPointService
{
    // 所需參數
    public $parameters = array(
        'TimeStamp'		=>'',
        'MerchantID'		=>'',
        'CheckMacValue'		=>'',
        'InvoiceNo'		=>'',
        'AllowanceNo'		=>'',
        'NotifyMail'		=>'',
        'Phone'			=>'',
        'Notify'		=>'',
        'InvoiceTag'		=>'',
        'Notified'		=>''
    );

    // 需要做urlencode的參數
    public $urlencode_field = array(
        'NotifyMail' 		=>''
    );

    // 不需要送壓碼的欄位
    public $none_verification = array(
        'CheckMacValue'		=>''
    );

    /**
     * 1寫入參數
     */
    public function insertString(array $arParameters): array
    {

        foreach ($this->parameters as $key => $value) {

            if(isset($arParameters[$key])) {
                $this->parameters[$key] = $arParameters[$key];
            }
        }

        return $this->parameters ;
    }

    /**
     * 2-2 驗證參數格式
     */
    public function checkExtendString(array $arParameters): array
    {

        $arErrors = array();

        // 37.發票號碼 InvoiceNo
        if( ( $arParameters['InvoiceTag'] == FunPointInvoiceTagType::Invoice ) || ( $arParameters['InvoiceTag'] == FunPointInvoiceTagType::Invoice_Void ) ) {

            // *必填項目
            if(strlen($arParameters['InvoiceNo']) == 0 ) {
                array_push($arErrors, '37:InvoiceNo is required.');
            }

            // *預設長度固定10碼
            if (strlen($arParameters['InvoiceNo']) != 10) {
                array_push($arErrors, '37:InvoiceNo length as 10.');
            }
        }

        // 44.折讓編號 AllowanceNo
        if( ( $arParameters['InvoiceTag'] == FunPointInvoiceTagType::Allowance ) || ( $arParameters['InvoiceTag'] == FunPointInvoiceTagType::Allowance_Void ) ) {

            if(strlen($arParameters['AllowanceNo']) == 0) {
                array_push($arErrors, "44:AllowanceNo is required.");
            }

            // *若有值長度固定16字元
            if(strlen($arParameters['AllowanceNo']) != 0 && strlen($arParameters['AllowanceNo']) != 16 ) {
                array_push($arErrors, '44:AllowanceNo length as 16.');
            }
        }

        // 45.NotifyMail 發送電子信箱

        // *若客戶電子信箱有值時，則格式僅能為Email的標準格式
        if(strlen($arParameters['NotifyMail']) > 0 ) {

            if ( !preg_match('/^[a-z0-9&\'\.\-_\+]+@[a-z0-9\-_]+\.([a-z0-9\-_]+\.)*?[a-z]+$/is', $arParameters['NotifyMail']) ) {
                array_push($arErrors, '45:Invalid Email Format.');
            }
        }

        // *下述情況通知電子信箱不可為空值(發送方式為E-電子郵件)
        if( $arParameters['Notify'] == FunPointNotifyType::Email && strlen($arParameters['NotifyMail']) == 0 ) {
            array_push($arErrors, "39:NotifyMail is required.");
        }

        // 46.通知手機號碼 NotifyPhone
        // *若客戶手機號碼有值時，則預設格式為數字組成
        if(strlen($arParameters['Phone']) > 0 ) {
            if ( !preg_match('/^[0-9]*$/', $arParameters['Phone']) ) {
                array_push($arErrors, '46:Invalid Phone.');
            }
        }

        // *最大長度為20碼
        if(strlen($arParameters['Phone']) > 20 ) {
            array_push($arErrors, "46:Phone max length as 20.");
        }

        // *下述情況通知手機號碼不可為空值(發送方式為S-簡訊)
        if( $arParameters['Notify'] == FunPointNotifyType::Sms && strlen($arParameters['Phone']) == 0 ) {
            array_push($arErrors, "46:Phone is required.");
        }

        // 45-46 發送簡訊號碼、發送電子信箱不能全為空值
        if(strlen($arParameters['Phone']) == 0 && strlen($arParameters['NotifyMail']) == 0) {
            array_push($arErrors, "45-46:NotifyMail or Phone is required.");
        } else {
            if( $arParameters['Notify'] == FunPointNotifyType::All && ( strlen($arParameters['NotifyMail']) == 0 || strlen($arParameters['Phone']) == 0 ) ) {
                array_push($arErrors, "45-46:NotifyMail and Phone is required.");
            }
        }

        // 47. 發送方式 Notify

        // *固定給定下述預設值
        if( ($arParameters['Notify'] != FunPointNotifyType::Sms ) && ( $arParameters['Notify'] != FunPointNotifyType::Email ) && ( $arParameters['Notify'] != FunPointNotifyType::All ) ) {
            array_push($arErrors, "47:Notify is required.");
        }

        // 48.發送內容類型 InvoiceTag
        // *固定給定下述預設值
        if( ( $arParameters['InvoiceTag'] != FunPointInvoiceTagType::Invoice ) && ( $arParameters['InvoiceTag'] != FunPointInvoiceTagType::Invoice_Void ) && ( $arParameters['InvoiceTag'] != FunPointInvoiceTagType::Allowance ) && ( $arParameters['InvoiceTag'] != FunPointInvoiceTagType::Allowance_Void ) && ( $arParameters['InvoiceTag'] != FunPointInvoiceTagType::Invoice_Winning ) ) {
            array_push($arErrors, "48:InvoiceTag is required.");
        }

        // 49.發送對象 Notified
        // *固定給定下述預設值
        if( ( $arParameters['Notified'] != FunPointNotifiedType::Customer ) && ( $arParameters['Notified'] != FunPointNotifiedType::vendor ) && ( $arParameters['Notified'] != FunPointNotifiedType::All ) ) {
            array_push($arErrors, "49:Notified is required.");
        }

        if(sizeof($arErrors)>0) {
            throw new FunPointException(join('<br>', $arErrors));
        }

        return $arParameters ;
    }

    /**
     * 4欄位例外處理方式(送壓碼前)
     */
    public function checkException(array $arParameters): array
    {
        return $arParameters ;
    }
}
