<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

final class SomeClass
{
    public function getData()
    {
        return $this->getNumber();
    }

    private function getNumber(): int
    {
        return 1000;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

final class SomeClass
{
    public function getData(): int
    {
        return $this->getNumber();
    }

    private function getNumber(): int
    {
        return 1000;
    }
}

?>
