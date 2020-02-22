<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayParamDocTypeRector\Fixture;

class KeepMixed2
{
    /**
     * @param array        $metadata
     * @param mixed[]|null $metadata
     *
     * @return KeepMixed2
     */
    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }
}
