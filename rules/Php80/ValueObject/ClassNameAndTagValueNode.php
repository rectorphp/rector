<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Php80\ValueObject;

use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
final class ClassNameAndTagValueNode
{
    /**
     * @readonly
     * @var string
     */
    private $enumClass;
    /**
     * @readonly
     * @var \PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode|\PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode|\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode
     */
    private $tagValueNode;
    /**
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
