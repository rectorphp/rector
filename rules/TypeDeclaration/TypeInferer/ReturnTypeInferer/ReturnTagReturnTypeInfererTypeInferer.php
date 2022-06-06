<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;

use RectorPrefix20220606\PhpParser\Node\FunctionLike;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use RectorPrefix20220606\Rector\TypeDeclaration\Contract\TypeInferer\ReturnTypeInfererInterface;
final class ReturnTagReturnTypeInfererTypeInferer implements ReturnTypeInfererInterface
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function inferFunctionLike(FunctionLike $functionLike) : Type
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($functionLike);
        return $phpDocInfo->getReturnType();
    }
    public function getPriority() : int
    {
        return 400;
    }
}
