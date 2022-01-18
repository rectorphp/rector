<?php

namespace Rector\Tests\Privatization\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector\Fixture;

class SkipAttribute
{
    #[ORM\Column(type: 'json')]
    private string $parameters = '{}';
}
