<?php

declare(strict_types=1);

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

final class SkipAssignOnItsVarNotDirectlyUsedInNextReturn
{
    public const STATUS_A = 'a';
    public const STATUS_B = 'b';
    public const STATUS_C = 'c';
    public const STATUS_D = 'd';

    public function run(): \Closure
    {
        return static function (self $item) {
            $class = [];
            switch ($item->status) {
                    case self::STATUS_A:
                            $class[] = 'green';
                            break;

                    case self::STATUS_B:
                    case self::STATUS_C:
                    case self::STATUS_D:
                            $class[] = 'red';
                            break;
            }

            return execute($class);
        };
    }
}
