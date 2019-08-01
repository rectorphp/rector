<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\ReturnTypeResolver;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\NodeTypeResolver\Php\ReturnTypeInfo;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\PhpParser\Node\Manipulator\FunctionLikeManipulator;

final class ReturnTypeResolver
{
    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @var FunctionLikeManipulator
     */
    private $functionLikeManipulator;

    public function __construct(
        DocBlockManipulator $docBlockManipulator,
        FunctionLikeManipulator $functionLikeManipulator
    ) {
        $this->docBlockManipulator = $docBlockManipulator;
        $this->functionLikeManipulator = $functionLikeManipulator;
    }

    /**
     * @param ClassMethod|Function_ $functionLike
     */
    public function resolveFunctionLikeReturnType(FunctionLike $functionLike): ?ReturnTypeInfo
    {
        $docReturnTypeInfo = $this->docBlockManipulator->getReturnTypeInfo($functionLike);
        $codeReturnTypeInfo = $this->functionLikeManipulator->resolveStaticReturnTypeInfo($functionLike);

        // code has priority over docblock
        if ($docReturnTypeInfo === null) {
            return $codeReturnTypeInfo;
        }

        if ($codeReturnTypeInfo && $codeReturnTypeInfo->getTypeNode()) {
            return $codeReturnTypeInfo;
        }

        return $docReturnTypeInfo;
    }
}
