<?php

declare(strict_types=1);

namespace Rector\Php72\Tests\Rector\FuncCall\GetClassOnNullRector\Fixture;

trait SomeTrait
{
    /**
     * @return mixed
     */
    private function someMethodInTrait(?object $objectInTrait)
    {
        if (null === $objectInTrait) {
            return null;
        }

        return get_class($objectInTrait);
    }
}

final class SomeClassUsingTheTrait
{
    use SomeTrait;

    public function __invoke()
    {
        $object = new \stdClass();
        $object->id = 42;

        $fromClass = $this->someMethodInClass($object);
        $fromTrait = $this->someMethodInTrait($object);
    }

    public function someMethodInClass(?object $objectInClass)
    {
        if (null === $objectInClass) {
            return null;
        }

        return get_class($objectInClass);
    }
}
