<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;


use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source\Static_\BaseFinderStatic;

final class UnionStaticStrict extends BaseFinderStatic
{
    public function something()
    {
        return $this->find();
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;


use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source\Static_\BaseFinderStatic;

final class UnionStaticStrict extends BaseFinderStatic
{
    /**
     * @return bool|\Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture\UnionStaticStrict
     */
    public function something()
    {
        return $this->find();
    }
}

?>
