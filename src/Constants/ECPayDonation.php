<?php
namespace TsaiYiHua\FunPoint\Constants;

/**
 * 電子發票捐贈註記
 */
class FunPointDonation
{
    // 捐贈
    const Yes = '1';

    // 不捐贈
    const No = '0';

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
