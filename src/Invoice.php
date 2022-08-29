<?php
/**
 * Created by PhpStorm.
 * User: yihua
 * Date: 2018/11/27
 * Time: 下午 3:30
 */

namespace TsaiYiHua\FunPoint;

use TsaiYiHua\FunPoint\Collections\InvoicePostCollection;
use TsaiYiHua\FunPoint\Libs\FunPointInvoice;

class Invoice
{
    use FunPointTrait;

    protected $apiUrl;
    protected $merchantId;
    protected $hashKey;
    protected $hashIv;
    protected $encryptType='md5';

    protected $checkMacValueIgnoreFields;

    public function __construct(protected InvoicePostCollection $postData, public FunPointInvoice $ecpayInvoice)
    {
        if (config('app.env') == 'production') {
            $this->apiUrl = 'https://einvoice.ecpay.com.tw/Invoice/Issue';
        } else {
            $this->apiUrl = 'https://einvoice-stage.ecpay.com.tw/Invoice/Issue';
        }
        $this->ecpayInvoice->Invoice_Method = 'INVOICE' ;
        $this->ecpayInvoice->Invoice_Url = $this->apiUrl;
        $this->ecpayInvoice->MerchantID = config('ecpay.MerchantId');
        $this->ecpayInvoice->HashKey = config('ecpay.InvoiceHashKey');
        $this->ecpayInvoice->HashIV = config('ecpay.InvoiceHashIV');
    }

    /**
     * @param $invData
     * @return $this
     * @throws Exceptions\FunPointException
     */
    public function setPostData($invData)
    {
        $this->postData->setData($invData)->setPostRawData();
        foreach($this->postData->all() as $key=>$val) {
            $this->ecpayInvoice->Send[$key] = $val;
        }
        return $this;
    }
}
