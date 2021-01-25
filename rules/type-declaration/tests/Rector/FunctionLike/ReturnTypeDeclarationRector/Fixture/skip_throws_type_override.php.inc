<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

interface RespectInterface
{
    /**
     * @return string
     */
    public function toString(): string;
}

class SkipThrowsTypeOverride implements RespectInterface
{
    /**
     * @return string
     */
    public function toString(): string
    {
        throw new \RuntimeException('This method returns void but the return type hint has to remain "string".');
    }
}
