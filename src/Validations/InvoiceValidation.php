<?php
namespace TsaiYiHua\FunPoint\Validations;

use Illuminate\Support\Facades\Validator;
use TsaiYiHua\FunPoint\Constants\FunPointCarruerType;
use TsaiYiHua\FunPoint\Constants\FunPointClearanceMark;
use TsaiYiHua\FunPoint\Constants\FunPointDonation;
use TsaiYiHua\FunPoint\Constants\FunPointInvType;
use TsaiYiHua\FunPoint\Constants\FunPointPrintMark;
use TsaiYiHua\FunPoint\Constants\FunPointTaxType;

class InvoiceValidation
{
    /**
     * Validation for invoice post data
     * @param $data
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     */
    static public function invoiceValidator($data)
    {
        /**
         * items[] = [
         *      'name' => 'abc',
         *      'qty' => 2,
         *      'unit' => 'piece',
         *      'price' => 50
         * ];
         */
        $validator = Validator::make($data, [
            'Items' => 'required|array',
            'OrderId' => 'alpha_num|max:30',
            'CustomerID' => 'alpha_dash|max:20',
            'CustomerIdentifier' => 'digits:8',
            'CustomerName' => 'required_if:Print,1|max:60',
            'CustomerAddr' => 'required_if:Print,1|max:200',
            'CustomerPhone' => 'required_if:CustomerEmail,null|max:20',
            'CustomerEmail' => 'required_if:CustomerPhone,null|max:200',
            'ClearanceMark' => 'in:'.implode(',', FunPointClearanceMark::getConstantValues()->toArray()),
            'TaxType' => 'in:'.implode(',', FunPointTaxType::getConstantValues()->toArray()),
            'CarruerType' => 'in:'.implode(',', FunPointCarruerType::getConstantValues()->toArray()),
            'CarruerNum' => 'max:64',
            'Donation' => 'in:'.implode(',', FunPointDonation::getConstantValues()->toArray()),
            'LoveCode' => 'required_if:Donation,1|max:7',
            'Print' => 'in:'.implode(',', FunPointPrintMark::getConstantValues()->toArray()),
            'DelayDay' => 'int|min:0|max:15',
            'InvType' => 'in:'.implode(',', FunPointInvType::getConstantValues()->toArray())
        ]);
        return $validator;
    }
}
