<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\PropertyTypeDeclarationRector\Fixture;

class PhpstanClassStringType
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
