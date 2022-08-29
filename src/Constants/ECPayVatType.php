<?php


namespace TsaiYiHua\FunPoint\Constants;


class FunPointVatType
{
    // 商品單價含稅價
    const Yes = '1';

    // 商品單價未稅價
    const No = '0';

    /**
     * @return \Illuminate\Support\Collection
     */
    static public function getConstantValues()
    {
        return collect([
            self::Yes,
            self::No
        ])->unique();
    }
}
