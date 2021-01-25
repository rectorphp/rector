<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeTraverser;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\VoidType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\Contract\TypeInferer\ReturnTypeInfererInterface;
use Rector\TypeDeclaration\TypeInferer\AbstractTypeInferer;
use Rector\TypeDeclaration\TypeInferer\SilentVoidResolver;

final class ReturnedNodesReturnTypeInferer extends AbstractTypeInferer implements ReturnTypeInfererInterface
{
    /**
     * @var Type[]
     */
    private $types = [];

    /**
     * @var SilentVoidResolver
     */
    private $silentVoidResolver;

    public function __construct(SilentVoidResolver $silentVoidResolver)
    {
        $this->silentVoidResolver = $silentVoidResolver;
    }

    /**
     * @param ClassMethod|Closure|Function_ $functionLike
     */
    public function inferFunctionLike(FunctionLike $functionLike): Type
    {
        /** @var Class_|Trait_|Interface_|null $classLike */
        $classLike = $functionLike->getAttribute(AttributeKey::CLASS_NODE);
        if ($classLike === null) {
            return new MixedType();
        }

        if ($functionLike instanceof ClassMethod && $classLike instanceof Interface_) {
            return new MixedType();
        }

        $this->types = [];

        $localReturnNodes = $this->collectReturns($functionLike);
        if ($localReturnNodes === []) {
            return $this->resolveNoLocalReturnNodes($classLike, $functionLike);
        }

        $hasSilentVoid = $this->silentVoidResolver->hasSilentVoid($functionLike);

        foreach ($localReturnNodes as $localReturnNode) {
            if ($localReturnNode->expr === null) {
                $this->types[] = new VoidType();
                continue;
            }

            $exprType = $this->nodeTypeResolver->getStaticType($localReturnNode->expr);
            $this->types[] = $exprType;
        }

        if ($hasSilentVoid) {
            $this->types[] = new VoidType();
        }

        return $this->typeFactory->createMixedPassedOrUnionType($this->types);
    }

    public function getPriority(): int
    {
        return 1000;
    }

    /**
     * @return Return_[]
     */
    private function collectReturns(FunctionLike $functionLike): array
    {
        $returns = [];

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $functionLike->getStmts(), function (
            Node $node
        ) use (&$returns): ?int {
            if ($node instanceof Switch_) {
                $this->processSwitch($node);
            }

            // skip Return_ nodes in nested functions or switch statements
            if ($node instanceof FunctionLike) {
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }

            if (! $node instanceof Return_) {
                return null;
            }

            $returns[] = $node;

            return null;
        });

        return $returns;
    }

    private function resolveNoLocalReturnNodes(ClassLike $classLike, FunctionLike $functionLike): Type
    {
        // void type
        if (! $this->isAbstractMethod($classLike, $functionLike)) {
            return new VoidType();
        }

        return new MixedType();
    }

    private function processSwitch(Switch_ $switch): void
    {
        foreach ($switch->cases as $case) {
            if ($case->cond === null) {
                return;
            }
        }

        $this->types[] = new VoidType();
    }

    private function isAbstractMethod(ClassLike $classLike, FunctionLike $functionLike): bool
    {
        if ($functionLike instanceof ClassMethod && $functionLike->isAbstract()) {
            return true;
        }

        if (! $classLike instanceof Class_) {
            return false;
        }
        return $classLike->isAbstract();
    }
}
