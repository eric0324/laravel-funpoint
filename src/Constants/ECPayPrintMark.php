<?php
namespace TsaiYiHua\FunPoint\Constants;

/**
 * 電子發票列印註記
 */
class FunPointPrintMark
{
    // 不列印
    const No = '0';

    // 列印
    const Yes = '1';

    /**
     * @return \Illuminate\Support\Collection
     */
    static public function getConstantValues()
    {
        return collect([
            self::No,
            self::Yes
        ])->unique();
    }
}