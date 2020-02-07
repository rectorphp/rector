<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ClassAndInterfaceTypeResolver\Source;

new class extends ParentClass implements SomeInterface
{
    use AnotherTrait;
};
