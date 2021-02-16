<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

final class SkipOnceTypeAndOnceNot
{
    public function getData()
    {
        if (mt_rand(0, 100)) {
            return $this->getNumber();
        }

        return $this->getRandom();
    }

    private function getNumber(): int
    {
        return 100;
    }

    private function getRandom()
    {
        if (mt_rand(0, 1)) {
            return true;
        }

        return '...';
    }
}
