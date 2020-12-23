<?php

namespace Rector\Php80\Tests\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

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
