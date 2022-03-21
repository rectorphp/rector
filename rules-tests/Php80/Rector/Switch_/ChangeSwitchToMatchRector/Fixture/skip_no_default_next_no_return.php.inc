<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

class SkipNoDefaultNextNoReturn
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
    }
}
