<?php

namespace Rector\Tests\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector\Fixture;

final class KeepParentNotAutoloaded extends NotAutoloadedParent
{
    protected $value = 100;
}
