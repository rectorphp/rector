<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;

use PhpParser\Node\FunctionLike;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\TypeDeclaration\Contract\TypeInferer\ReturnTypeInfererInterface;

final class ReturnTagReturnTypeInferer implements ReturnTypeInfererInterface
{
    public function __construct(
        private PhpDocInfoFactory $phpDocInfoFactory
    ) {
    }

    public function inferFunctionLike(FunctionLike $functionLike): Type
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($functionLike);
        return $phpDocInfo->getReturnType();
    }

    public function getPriority(): int
    {
        return 400;
    }
}
