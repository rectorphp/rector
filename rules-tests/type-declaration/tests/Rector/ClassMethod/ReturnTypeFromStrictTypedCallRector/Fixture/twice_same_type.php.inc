<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

final class TwiceSameType
{
    public function getData()
    {
        if (mt_rand(0, 100)) {
            return $this->getNumber(100);
        }

        return $this->getNumber(10);
    }

    private function getNumber(int $value): int
    {
        return $value;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

final class TwiceSameType
{
    public function getData(): int
    {
        if (mt_rand(0, 100)) {
            return $this->getNumber(100);
        }

        return $this->getNumber(10);
    }

    private function getNumber(int $value): int
    {
        return $value;
    }
}

?>
