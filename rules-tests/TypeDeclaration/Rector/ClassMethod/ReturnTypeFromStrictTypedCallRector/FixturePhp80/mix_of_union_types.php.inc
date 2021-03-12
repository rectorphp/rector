<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\FixturePhp80;

final class MixOfUnionTypes
{
    public function getData()
    {
        if (mt_rand(0, 100)) {
            return $this->getFirstMix();
        }

        return $this->getSecondMixed();
    }

    private function getFirstMix(): int|string
    {
        return 100;
    }

    private function getSecondMixed(): string|bool
    {
        return 'hey';
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\FixturePhp80;

final class MixOfUnionTypes
{
    public function getData(): int|string|bool
    {
        if (mt_rand(0, 100)) {
            return $this->getFirstMix();
        }

        return $this->getSecondMixed();
    }

    private function getFirstMix(): int|string
    {
        return 100;
    }

    private function getSecondMixed(): string|bool
    {
        return 'hey';
    }
}

?>
