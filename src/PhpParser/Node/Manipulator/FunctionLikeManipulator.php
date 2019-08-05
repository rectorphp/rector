<?php declare(strict_types=1);

namespace Rector\PhpParser\Node\Manipulator;

use Iterator;
use PhpParser\Node;
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
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\Php\ReturnTypeInfo;
use Rector\Php\PhpVersionProvider;
use Rector\Php\TypeAnalyzer;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Node\Resolver\NameResolver;
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

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        TypeAnalyzer $typeAnalyzer,
        NodeTypeResolver $nodeTypeResolver,
        NameResolver $nameResolver,
        PhpVersionProvider $phpVersionProvider
    ) {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->typeAnalyzer = $typeAnalyzer;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nameResolver = $nameResolver;
        $this->phpVersionProvider = $phpVersionProvider;
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
        if ($functionLike->returnType !== null) {
            $types = $this->resolveReturnTypeToString($functionLike->returnType);

            // do not override freshly added type declaration
            if (! $functionLike->returnType->getAttribute(
                AbstractTypeDeclarationRector::HAS_NEW_INHERITED_TYPE
            ) && $types !== []) {
                return new ReturnTypeInfo($types, $this->typeAnalyzer);
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
            return new ReturnTypeInfo(['void'], $this->typeAnalyzer);
        }

        $types = array_filter($types);

        return new ReturnTypeInfo($types, $this->typeAnalyzer);
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
        /** @var Return_[] $returnNodes */
        $returnNodes = $this->betterNodeFinder->findInstanceOf((array) $functionLike->stmts, Return_::class);

        $types = [];
        foreach ($returnNodes as $returnNode) {
            if ($returnNode->expr === null) {
                continue;
            }

            $types = array_merge($types, $this->nodeTypeResolver->resolveSingleTypeToStrings($returnNode->expr));
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

        if (count($yieldNodes)) {
            $this->isVoid = false;

            if ($this->phpVersionProvider->isAtLeast('7.1')) {
                // @see https://www.php.net/manual/en/language.types.iterable.php
                return ['iterable'];
            }

            return [Iterator::class];
        }

        return [];
    }
}
