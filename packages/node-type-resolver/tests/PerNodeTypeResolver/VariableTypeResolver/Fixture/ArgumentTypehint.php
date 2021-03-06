<?php

namespace SomeNamespace;

use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\VariableTypeResolver\Fixture\AnotherType;

array_map(function (AnotherType $useUse) {
    return $useUse;
}, []);
