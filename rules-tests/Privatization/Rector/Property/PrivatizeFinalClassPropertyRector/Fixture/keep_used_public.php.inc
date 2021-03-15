<?php

namespace Rector\Tests\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector\Fixture;

final class KeepUsedPublic
{
    public $value;
}

final class PublicUser
{
    public function run()
    {
        $keepUsedPublic = new KeepUsedPublic();
        $keepUsedPublic->value;
    }
}
