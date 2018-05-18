<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\Source;

trait SomeTrait
{
    public function getDoctrine(): SomeClass
    {
        return new SomeClass();
    }
}