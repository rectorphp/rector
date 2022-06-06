<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;

use PhpParser\Node\FunctionLike;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\TypeDeclaration\Contract\TypeInferer\ReturnTypeInfererInterface;
final class ReturnTagReturnTypeInfererTypeInferer implements \Rector\TypeDeclaration\Contract\TypeInferer\ReturnTypeInfererInterface
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function inferFunctionLike(\PhpParser\Node\FunctionLike $functionLike) : \PHPStan\Type\Type
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($functionLike);
        return $phpDocInfo->getReturnType();
    }
    public function getPriority() : int
    {
        return 400;
    }
}
