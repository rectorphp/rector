<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

class SkipThrowenException
{
    public function save(): void
    {
        switch ('deleted') {
            case 'inserted':
                echo '1';
                break;

            case 'deleted':
                throw new \Exception();

        }
    }
}
