<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

final class AddFromChild
{
    public function getData(Nested $nested)
    {
        return $nested->getData();
    }
}

final class Nested
{
    public function getData()
    {
        return [
            'key',
            'value'
        ];
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

final class AddFromChild
{
    /**
     * @return string[]
     */
    public function getData(Nested $nested)
    {
        return $nested->getData();
    }
}

final class Nested
{
    /**
     * @return string[]
     */
    public function getData()
    {
        return [
            'key',
            'value'
        ];
    }
}

?>
