<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

class SkipNonEmptyArray
{
    /**
     * @var string[]
     */
    private $names = [];

    /**
     * @return string[]
     */
    public function getValues(): array
    {
        if ($this->names !== []) {
            return $this->names;
        }

        foreach ([1, 2, 3] as $value) {
            $this->names[] = (string) $value;
        }

        return $this->names;
    }
}
