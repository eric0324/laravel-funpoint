<?php


namespace TsaiYiHua\FunPoint\Constants;


class FunPointPayTypeCategory
{
    const Ecpay = '2';

    /**
     * @return \Illuminate\Support\Collection
     */
    static public function getConstantValues()
    {
        return collect([
            self::Ecpay
        ])->unique();
    }
}
