<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;

use PhpParser\Node\Expr\Closure;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\TypeDeclaration\Contract\TypeInferer\ReturnTypeInfererInterface;
use Rector\TypeDeclaration\TypeInferer\AbstractTypeInferer;

final class ReturnTagReturnTypeInferer extends AbstractTypeInferer implements ReturnTypeInfererInterface
{
    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    public function __construct(DocBlockManipulator $docBlockManipulator)
    {
        $this->docBlockManipulator = $docBlockManipulator;
    }

    /**
     * @param ClassMethod|Closure|Function_ $functionLike
     * @return string[]
     */
    public function inferFunctionLike(FunctionLike $functionLike): array
    {
        $returnTypeInfo = $this->docBlockManipulator->getReturnTypeInfo($functionLike);
        if ($returnTypeInfo === null) {
            return [];
        }

        $fqnTypes = $returnTypeInfo->getFqnTypes();
        if ($returnTypeInfo->isNullable()) {
            $fqnTypes[] = 'null';
        }

        return $fqnTypes;
    }

    public function getPriority(): int
    {
        return 400;
    }
}
