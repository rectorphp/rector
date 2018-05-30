<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ClassLikeTypeResolver\Source;

final class ClassWithTrait
{
    use AnotherTrait;
}

$someClass = new ClassWithTrait();