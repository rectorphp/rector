<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector\Fixture;

class SkipNullableSelfReferencingPropertySetter
{
    private ?self $parent = null;

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }
}
?>
