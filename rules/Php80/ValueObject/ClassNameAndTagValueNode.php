<?php

declare(strict_types=1);

namespace Rector\Php80\ValueObject;

use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;

final class ClassNameAndTagValueNode
{
    public function __construct(
        private readonly string $enumClass,
        private readonly ParamTagValueNode|ReturnTagValueNode|VarTagValueNode $tagValueNode
    ) {
    }

    public function getEnumClass(): string
    {
        return $this->enumClass;
    }

    public function getTagValueNode(): ParamTagValueNode|ReturnTagValueNode|VarTagValueNode
    {
        return $this->tagValueNode;
    }
}
