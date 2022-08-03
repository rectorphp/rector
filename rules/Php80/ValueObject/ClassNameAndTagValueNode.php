<?php

declare (strict_types=1);
namespace Rector\Php80\ValueObject;

use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
final class ClassNameAndTagValueNode
{
    /**
     * @var class-string
     * @readonly
     */
    private $enumClass;
    /**
     * @readonly
     * @var \PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode|\PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode|\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode
     */
    private $tagValueNode;
    /**
     * @param class-string $enumClass
     * @param \PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode|\PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode|\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode $tagValueNode
     */
    public function __construct(string $enumClass, $tagValueNode)
    {
        $this->enumClass = $enumClass;
        $this->tagValueNode = $tagValueNode;
    }
    public function getEnumClass() : string
    {
        return $this->enumClass;
    }
    /**
     * @return \PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode|\PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode|\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode
     */
    public function getTagValueNode()
    {
        return $this->tagValueNode;
    }
}
