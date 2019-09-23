<?php

namespace Rector\Php74\Tests\Rector\Closure\ClosureToArrowFunctionRector\Fixture;

class SkipNoReturn
{
    public function run($meetups)
    {
        return array_filter($meetups, function (Meetup $meetup) {
            is_object($meetup);
        });

        return array_filter($meetups, function (Meetup $meetup) {
            return;
        });
    }
}
