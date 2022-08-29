<?php
namespace TsaiYiHua\FunPoint;

use TsaiYiHua\FunPoint\Collections\CheckoutPostCollection;
use TsaiYiHua\FunPoint\Collections\InvoicePostCollection;
use TsaiYiHua\FunPoint\Exceptions\FunPointException;
use TsaiYiHua\FunPoint\Validations\PaymentValidation;

class Checkout
{
    use FunPointTrait;

    protected $apiUrl;
    protected $platform;
    protected $merchantId;
    protected $hashKey;
    protected $hashIv;
    protected $encryptType='sha256';
    protected $notifyUrl;

    protected $itemValidation;

    /**
     * @param CheckoutPostCollection $postData
     */
    public function __construct(protected CheckoutPostCollection $postData)
    {
        if (config('app.env') == 'production') {
            $this->apiUrl = 'https://payment.funpoint.com.tw/Cashier/AioCheckOut/V5';
        } else {
            $this->apiUrl = 'https://payment-stage.funpoint.com.tw/Cashier/AioCheckOut/V5';
        }
        $this->merchantId = config('ecpay.MerchantId');
        $this->hashKey = config('ecpay.HashKey');
        $this->hashIv = config('ecpay.HashIV');
    }

    /**
     * Handle the Post Data
     * @param array $data
     * @return $this
     * @throws FunPointException
     */
    public function setPostData(array $data)
    {
        $validator = PaymentValidation::postDataValidator($data);
        if ($validator->fails()) {
            throw new FunPointException($validator->getMessageBag());
        }
        $this->postData->setData($data)->setBasicInfo()->setOrderInfo()->setOptionalInfo()->optimize();
        return $this;
    }

    /**
     * 還沒用到
     * @param string $platformId
     * @return $this
     * @throws FunPointException
     */
    public function setPlatform(string $platformId)
    {
        if (strlen($platformId) > 10) {
            throw new FunPointException('PlatformId max length is 10');
        }
        $this->platform = $platformId;
        $this->postData->put('PlatformId', $platformId);
        return $this;
    }

    /**
     * @param string $installmentData
     * @return $this;
     * @throws FunPointException
     */
    public function withInstallment(string $installmentData)
    {
        $this->postData->setInstallment($installmentData);
        return $this;
    }

    /**
     * @param array $data
     * @return $this
     * @throws FunPointException
     */
    public function withPeriodAmount(array $data)
    {
        $this->postData->setPeriodAmount($data);
        return $this;
    }
    /**
     * 當要開立發票時，帶入此方法
     * @param array $invData
     * @return $this
     * @throws FunPointException
     */
    public function withInvoice(array $invData)
    {
        $this->postData->setInvoice($invData);
        return $this;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setNotifyUrl(string $url)
    {
        $this->postData->notifyUrl = $url;
        return $this;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setReturnUrl(string $url)
    {
        $this->postData->returnUrl = $url;
        return $this;
    }
}
