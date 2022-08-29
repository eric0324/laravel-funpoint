<?php
namespace TsaiYiHua\FunPoint;

use Illuminate\Support\Collection;
use TsaiYiHua\FunPoint\Exceptions\FunPointException;
use TsaiYiHua\FunPoint\Services\StringService;

trait FunPointTrait
{
    /**
     * Send data to FunPoint
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|array
     * @throws FunPointException
     */
    public function send()
    {
        if (class_basename(self::class) === 'Invoice') {
            return $this->ecpayInvoice->Check_Out();
        } else {
            $this->setCheckCodeValue();
            $data = [
                'apiUrl' => $this->apiUrl,
                'postData' => $this->postData
            ];
            if (FunPoint::$sendForm === null) {
                if (config('ecpay.SendForm') == null) {
                    return view('ecpay::send', $data);
                } else {
                    return view(config('ecpay.SendForm'), $data);
                }
            } else {
                return view(FunPoint::$sendForm, $data);
            }
        }
    }

    /**
     * Using CURL to send form data (For Query Info)
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws FunPointException
     */
    public function query()
    {
        $this->setCheckCodeValue();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->postData->all()));

        // 回傳參數
        $response = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpStatus == 200) {
            return $this->parseResponse($response);
        } else {
            throw new FunPointException('HTTP Error with code '.$httpStatus);
        }
    }

    /**
     * Get Post Data
     * @return mixed
     */
    public function getPostData()
    {
        return $this->postData;
    }

    /**
     * Set CheckMacValue to postData
     * @throws FunPointException
     */
    protected function setCheckCodeValue()
    {
        /** @var Collection $this->postData */
        if ($this->postData->isEmpty()) {
            throw new FunPointException('Post Data is Empty');
        }
        $hashData = [
            'key' => $this->hashKey,
            'iv' => $this->hashIv,
            'type' => $this->encryptType
        ];
        if (isset($this->checkMacValueIgnoreFields)) {
            $hashData['ignore'] = $this->checkMacValueIgnoreFields;
        }
        /** @var Collection $this->postData */
        $checkValue = StringService::checkMacValueGenerator($this->postData->all(), $hashData);
        /** @var Collection $this->postData */
        $this->postData->put('CheckMacValue', $checkValue);
    }

    /**
     * @param string $response
     * @return Collection
     * @throws FunPointException
     */
    protected function parseResponse($response)
    {
        $responseCollection = new Collection();
        preg_match_all('/([^&]*=[^&]*)/', $response, $match);
        if (!empty($match[0])) {
            foreach($match[0] as $paramValue) {
                $param = strstr($paramValue, '=', true);
                $value = substr(strstr($paramValue,'='), 1, 255);
                $responseCollection->put($param, $value);
            }
        } else {
            throw new FunPointException($response);
        }
        return $responseCollection;
    }
}
