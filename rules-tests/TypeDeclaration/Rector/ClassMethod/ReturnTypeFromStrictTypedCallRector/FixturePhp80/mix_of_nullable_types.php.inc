<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\FixturePhp80;

final class MixOfNullableTypes
{
    public function getData()
    {
        if (mt_rand(0, 100)) {
            return $this->getFirstMix();
        }

        return $this->getSecondMixed();
    }

    private function getFirstMix(): ?int
    {
        return 100;
    }

    private function getSecondMixed(): ?string
    {
        return 'hey';
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\FixturePhp80;

final class MixOfNullableTypes
{
    public function getData(): int|null|string
    {
        if (mt_rand(0, 100)) {
            return $this->getFirstMix();
        }

        return $this->getSecondMixed();
    }

    private function getFirstMix(): ?int
    {
        return 100;
    }

    private function getSecondMixed(): ?string
    {
        return 'hey';
    }
}

?>
