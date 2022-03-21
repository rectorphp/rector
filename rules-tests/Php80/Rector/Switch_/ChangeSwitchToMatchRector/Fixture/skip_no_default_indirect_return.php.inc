<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

class SkipNoDefaultIndirectReturn
{
    public function run($value)
    {
        if (strlen($value) < 3) {
            switch($value) {
                case 'A':
                case 'B':
                    return 'AB';
                case 'C':
                    return 'D';

            }
        }

        $value = 'new value';
        return $value;
    }
}
