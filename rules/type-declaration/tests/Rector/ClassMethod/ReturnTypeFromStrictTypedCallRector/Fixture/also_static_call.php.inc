<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

final class AlsoStaticCall
{
    public function getData()
    {
        return ValuableData::getNumber();
    }
}

final class ValuableData
{
    public static function getNumber(): int
    {
        return 1000;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

final class AlsoStaticCall
{
    public function getData(): int
    {
        return ValuableData::getNumber();
    }
}

final class ValuableData
{
    public static function getNumber(): int
    {
        return 1000;
    }
}

?>
