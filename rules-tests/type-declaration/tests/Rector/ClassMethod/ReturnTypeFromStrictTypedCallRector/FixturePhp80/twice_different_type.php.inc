<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\FixturePhp80;

final class TwiceDifferentType
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

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\FixturePhp80;

final class TwiceDifferentType
{
    public function getData(): int|string
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

?>
