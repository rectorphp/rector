<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayParamDocTypeRector\Fixture;

class KeepFilled
{
    /**
     * @var string[]
     */
    private $values;

    /**
     * @param string[] $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }
}
