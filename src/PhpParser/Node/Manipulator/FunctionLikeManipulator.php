<?php declare(strict_types=1);

namespace Rector\PhpParser\Node\Manipulator;

use Iterator;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeTraverser;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\Php\ReturnTypeInfo;
use Rector\Php\PhpVersionProvider;
use Rector\Php\TypeAnalyzer;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\TypeDeclaration\Rector\FunctionLike\AbstractTypeDeclarationRector;

final class FunctionLikeManipulator
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var TypeAnalyzer
     */
    private $typeAnalyzer;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var bool
     */
    private $isVoid = false;

    /**
     * @var PhpVersionProvider
     */
    private $phpVersionProvider;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var PropertyFetchManipulator
     */
    private $propertyFetchManipulator;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        TypeAnalyzer $typeAnalyzer,
        NodeTypeResolver $nodeTypeResolver,
        NameResolver $nameResolver,
        PhpVersionProvider $phpVersionProvider,
        CallableNodeTraverser $callableNodeTraverser,
        PropertyFetchManipulator $propertyFetchManipulator
    ) {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->typeAnalyzer = $typeAnalyzer;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nameResolver = $nameResolver;
        $this->phpVersionProvider = $phpVersionProvider;
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->propertyFetchManipulator = $propertyFetchManipulator;
    }

    /**
     * Based on static analysis of code, looking for return types
     * @param ClassMethod|Function_|Closure $functionLike
     */
    public function resolveStaticReturnTypeInfo(FunctionLike $functionLike): ?ReturnTypeInfo
    {
        if ($this->shouldSkip($functionLike)) {
            return null;
        }

        // A. resolve from function return type
        // skip "array" to get more precise types
        if ($functionLike->returnType !== null && ! $this->nameResolver->isNames(
            $functionLike->returnType,
            ['array', 'iterable']
        )) {
            $types = $this->resolveReturnTypeToString($functionLike->returnType);

            // do not override freshly added type declaration
            if (! $functionLike->returnType->getAttribute(
                AbstractTypeDeclarationRector::HAS_NEW_INHERITED_TYPE
            ) && $types !== []) {
                return new ReturnTypeInfo($types, $this->typeAnalyzer, $types);
            }
        }

        $this->isVoid = true;

        // B. resolve from return $x nodes
        $types = $this->resolveTypesFromReturnNodes($functionLike);

        // C. resolve from yields
        if ($types === []) {
            $types = $this->resolveFromYieldNodes($functionLike);
        }

        if ($this->isVoid) {
            return new ReturnTypeInfo(['void'], $this->typeAnalyzer, ['void']);
        }

        $types = array_filter($types);

        return new ReturnTypeInfo($types, $this->typeAnalyzer, $types);
    }

    /**
     * @return string[]
     */
    public function getReturnedLocalPropertyNames(FunctionLike $functionLike): array
    {
        // process only class methods
        if ($functionLike instanceof Function_) {
            return [];
        }

        $returnedLocalPropertyNames = [];
        $this->callableNodeTraverser->traverseNodesWithCallable($functionLike, function (Node $node) use (
            &$returnedLocalPropertyNames
        ) {
            if (! $node instanceof Return_ || $node->expr === null) {
                return null;
            }

            if (! $this->propertyFetchManipulator->isLocalProperty($node->expr)) {
                return null;
            }

            $propertyName = $this->nameResolver->getName($node->expr);
            if ($propertyName === null) {
                return null;
            }

            $returnedLocalPropertyNames[] = $propertyName;
        });

        return $returnedLocalPropertyNames;
    }

    private function shouldSkip(FunctionLike $functionLike): bool
    {
        if (! $functionLike instanceof ClassMethod) {
            return false;
        }

        $classNode = $functionLike->getAttribute(AttributeKey::CLASS_NODE);
        // only class or trait method body can be analyzed for returns
        if ($classNode instanceof Interface_) {
            return true;
        }

        // only methods that are not abstract can be analyzed for returns
        return $functionLike->isAbstract();
    }

    /**
     * @param Identifier|Name|NullableType $node
     * @return string[]
     */
    private function resolveReturnTypeToString(Node $node): array
    {
        $types = [];

        $type = $node instanceof NullableType ? $node->type : $node;
        $result = $this->nameResolver->getName($type);
        if ($result !== null) {
            $types[] = $result;
        }

        if ($node instanceof NullableType) {
            $types[] = 'null';
        }

        return $types;
    }

    /**
     * @param ClassMethod|Function_|Closure $functionLike
     * @return string[]
     */
    private function resolveTypesFromReturnNodes(FunctionLike $functionLike): array
    {
        // local
        /** @var Return_[] $localReturnNodes */
        $localReturnNodes = [];

        $this->callableNodeTraverser->traverseNodesWithCallable((array) $functionLike->stmts, function (Node $node) use (
            &$localReturnNodes
        ): ?int {
            if ($node instanceof Function_ || $node instanceof Closure || $node instanceof ArrowFunction) {
                // skip Return_ nodes in nested functions
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }

            if (! $node instanceof Return_) {
                return null;
            }

            // skip void returns
            if ($node->expr === null) {
                return null;
            }

            $localReturnNodes[] = $node;

            return null;
        });

        $types = [];
        foreach ($localReturnNodes as $localReturnNode) {
            $types = array_merge($types, $this->nodeTypeResolver->resolveSingleTypeToStrings($localReturnNode->expr));
            $this->isVoid = false;
        }

        return $types;
    }

    /**
     * @param ClassMethod|Function_|Closure $functionLike
     * @return string[]
     */
    private function resolveFromYieldNodes(FunctionLike $functionLike): array
    {
        /** @var Yield_[] $yieldNodes */
        $yieldNodes = $this->betterNodeFinder->findInstanceOf((array) $functionLike->stmts, Yield_::class);

        $types = [];
        if (count($yieldNodes)) {
            $this->isVoid = false;

            foreach ($yieldNodes as $yieldNode) {
                if ($yieldNode->value === null) {
                    continue;
                }

                $resolvedTypes = $this->nodeTypeResolver->resolveSingleTypeToStrings($yieldNode->value);
                foreach ($resolvedTypes as $resolvedType) {
                    $types[] = $resolvedType . '[]';
                }
            }

            if ($this->phpVersionProvider->isAtLeast('7.1')) {
                // @see https://www.php.net/manual/en/language.types.iterable.php
                $types[] = 'iterable';
            } else {
                $types[] = Iterator::class;
            }
        }

        return array_unique($types);
    }
}
