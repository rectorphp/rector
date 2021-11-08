<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class SkipNullableSelfReferencingPropertyGetter
{
    private ?self $parent = null;

    public function getParent(): ?self
    {
        return $this->parent;
    }
}
?>
