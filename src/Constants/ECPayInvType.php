<?php
namespace TsaiYiHua\FunPoint\Constants;

/**
 * 字軌類別
 */
class FunPointInvType
{
    // 一般稅額
    const General = '07';

    /**
     * @return \Illuminate\Support\Collection
     */
    static public function getConstantValues()
    {
        return collect([
            self::General
        ])->unique();
    }
}
