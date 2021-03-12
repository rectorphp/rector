<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\TraitTypeResolver\Source;

trait TraitWithTrait
{
    use AnotherTrait;
}
