<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

final class SkipTwiceDifferentType
{
    public function getData()
    {
        if (mt_rand(0, 100)) {
            return $this->getNumber();
        }

        return $this->getString();
    }

    private function getNumber(): int
    {
        return 100;
    }

    private function getString(): string
    {
        return 'hey';
    }
}
