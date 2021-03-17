<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\ClassAndInterfaceTypeResolver\Source;

new class extends ParentClass implements SomeInterface
{
    use AnotherTrait;
};
