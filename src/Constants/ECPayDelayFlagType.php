<?php


namespace TsaiYiHua\FunPoint\Constants;


class FunPointDelayFlagType
{
    // 延遲註記
    const Delay = '1';

    // 觸發註記
    const Trigger = '2';

    /**
     * @return \Illuminate\Support\Collection
     */
    static public function getConstantValues()
    {
        return collect([
            self::Delay,
            self::Trigger
        ])->unique();
    }

}
