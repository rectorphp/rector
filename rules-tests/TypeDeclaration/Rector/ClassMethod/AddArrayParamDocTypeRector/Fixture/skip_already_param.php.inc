<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector\Fixture;

final class SkipAlreadyParam
{
    /**
     * @var string|null
     */
    private $rectorClass;

    public function setRectorClass(string $rectorClass)
    {
        $this->rectorClass = $rectorClass;
    }

    public function getRectorClass(): ?string
    {
        return $this->rectorClass;
    }
}
