<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\VariableTypeResolver\Fixture;

use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\VariableTypeResolver\Source\AnotherType;

array_map(function (AnotherType $useUse) {
    return $useUse;
}, [new AnotherType]);
