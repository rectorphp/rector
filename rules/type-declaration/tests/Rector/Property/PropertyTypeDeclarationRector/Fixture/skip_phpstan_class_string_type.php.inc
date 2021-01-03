<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector\Fixture;

class SkipPhpstanClassStringType
{
    /**
     * @var class-string
     */
    private $someFQCN;

    /**
     * @param class-string $someFQCN
     */
    public function __construct(string $someFQCN)
    {
        $this->someFQCN = $someFQCN;
    }
}
