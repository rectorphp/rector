<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayParamDocTypeRector\Fixture;

class KeepMixed
{
    /**
     * @var mixed[]
     */
    private $values;

    /**
     * @param mixed[] $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }
}
