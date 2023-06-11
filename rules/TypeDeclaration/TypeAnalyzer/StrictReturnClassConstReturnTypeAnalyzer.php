<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeAnalyzer;

use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\TypeDeclaration\NodeAnalyzer\ReturnTypeAnalyzer\AlwaysStrictReturnAnalyzer;
final class StrictReturnClassConstReturnTypeAnalyzer
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\ReturnTypeAnalyzer\AlwaysStrictReturnAnalyzer
     */
    private $alwaysStrictReturnAnalyzer;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    public function __construct(AlwaysStrictReturnAnalyzer $alwaysStrictReturnAnalyzer, NodeTypeResolver $nodeTypeResolver, TypeFactory $typeFactory)
    {
        $this->alwaysStrictReturnAnalyzer = $alwaysStrictReturnAnalyzer;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->typeFactory = $typeFactory;
    }
    public function matchAlwaysReturnConstFetch(ClassMethod $classMethod) : ?Type
    {
        $returns = $this->alwaysStrictReturnAnalyzer->matchAlwaysStrictReturns($classMethod);
        if ($returns === null) {
            return null;
        }
        $classConstFetchTypes = [];
        foreach ($returns as $return) {
            // @todo ~30 mins paid
            if (!$return->expr instanceof ClassConstFetch) {
                return null;
            }
            $classConstFetchTypes[] = $this->nodeTypeResolver->getType($return->expr);
        }
        return $this->typeFactory->createMixedPassedOrUnionType($classConstFetchTypes);
    }
}
