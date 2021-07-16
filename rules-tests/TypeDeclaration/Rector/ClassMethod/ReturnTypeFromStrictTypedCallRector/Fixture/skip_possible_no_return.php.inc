<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

final class SkipPossibleNoReturn
{
    public function getData()
    {
        if (mt_rand(0, 100)) {
            return $this->getNumber();
        }
    }

    private function getNumber(): int
    {
        return 100;
    }
}
