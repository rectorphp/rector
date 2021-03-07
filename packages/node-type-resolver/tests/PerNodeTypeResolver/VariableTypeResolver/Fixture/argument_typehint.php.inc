<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\VariableTypeResolver\Fixture;

use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\VariableTypeResolver\Source\AnotherType;

array_map(function (AnotherType $useUse) {
    return $useUse;
}, []);
