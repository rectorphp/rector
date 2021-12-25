<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByParentCallTypeRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByParentCallTypeRector\Source\ParentMethodWithUnion;

final class UnionParentType extends ParentMethodWithUnion
{
    public function getById($id)
    {
        parent::getById($id);
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByParentCallTypeRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByParentCallTypeRector\Source\ParentMethodWithUnion;

final class UnionParentType extends ParentMethodWithUnion
{
    public function getById(int|string $id)
    {
        parent::getById($id);
    }
}

?>
