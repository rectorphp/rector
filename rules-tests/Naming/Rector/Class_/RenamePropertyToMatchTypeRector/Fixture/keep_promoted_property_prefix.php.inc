<?php

namespace Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\Fixture;

use Rector\Symfony\DataProvider\ServiceMapProvider;

final class KeepPromotedPropertyPrefix
{
    public function __construct(
        private ServiceMapProvider $applicationServiceMapProvider,
    ) {
    }

    public function getApplicationServiceMapProvider(): ServiceMapProvider
    {
        return $this->applicationServiceMapProvider;
    }
}
