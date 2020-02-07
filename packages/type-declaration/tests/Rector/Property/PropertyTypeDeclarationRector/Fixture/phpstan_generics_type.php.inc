<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\PropertyTypeDeclarationRector\Fixture;

class PhpstanGenericsType
{
    /**
     * @var \ArrayIterator<self>
     */
    private $twoOfMe;

    public function __construct()
    {
        $this->twoOfMe = new \ArrayIterator([new self(), new self()]);
    }
}
