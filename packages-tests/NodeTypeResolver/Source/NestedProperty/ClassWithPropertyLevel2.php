<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\Source\NestedProperty;

final class ClassWithPropertyLevel2
{
    /**
     * @var ClassWithPropertyLevel3
     */
    public $level3;
}
