<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use Rector\TypeDeclaration\ChildPopulator\ChildParamPopulator;
use Rector\TypeDeclaration\TypeInferer\ParamTypeInferer;
use Rector\TypeDeclaration\ValueObject\NewType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\ParamTypeDeclarationRectorTest
 */
final class ParamTypeDeclarationRector extends AbstractTypeDeclarationRector
{
    /**
     * @var ParamTypeInferer
     */
    private $paramTypeInferer;

    /**
     * @var ChildParamPopulator
     */
    private $childParamPopulator;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    public function __construct(
        ChildParamPopulator $childParamPopulator,
        ParamTypeInferer $paramTypeInferer,
        NodeTypeResolver $nodeTypeResolver,
        ReflectionProvider $reflectionProvider
    ) {
        $this->paramTypeInferer = $paramTypeInferer;
        $this->childParamPopulator = $childParamPopulator;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->reflectionProvider = $reflectionProvider;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change @param types to type declarations if not a BC-break',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class ParentClass
{
    /**
     * @param int $number
     */
    public function keep($number)
    {
    }
}

final class ChildClass extends ParentClass
{
    /**
     * @param int $number
     */
    public function keep($number)
    {
    }

    /**
     * @param int $number
     */
    public function change($number)
    {
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class ParentClass
{
    /**
     * @param int $number
     */
    public function keep($number)
    {
    }
}

final class ChildClass extends ParentClass
{
    /**
     * @param int $number
     */
    public function keep($number)
    {
    }

    /**
     * @param int $number
     */
    public function change(int $number)
    {
    }
}
CODE_SAMPLE
                ),

            ]);
    }

    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::SCALAR_TYPES)) {
            return null;
        }
        if ($node->params === null) {
            return null;
        }
        if ($node->params === []) {
            return null;
        }

        foreach ($node->params as $position => $param) {
            $this->refactorParam($param, $node, (int) $position);
        }

        return null;
    }

    /**
     * @param ClassMethod|Function_ $functionLike
     */
    private function refactorParam(Param $param, FunctionLike $functionLike, int $position): void
    {
        if ($this->shouldSkipParam($param, $functionLike, $position)) {
            return;
        }

        $inferedType = $this->paramTypeInferer->inferParam($param);
        if ($inferedType instanceof MixedType) {
            return;
        }

        if ($this->isTraitType($inferedType)) {
            return;
        }

        $paramTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode(
            $inferedType,
            PHPStanStaticTypeMapper::KIND_PARAM
        );

        if ($paramTypeNode === null) {
            return;
        }

        $param->type = $paramTypeNode;
        $this->childParamPopulator->populateChildClassMethod($functionLike, $position, $inferedType);
    }

    private function shouldSkipParam(Param $param, FunctionLike $functionLike, int $position): bool
    {
        if ($this->vendorLockResolver->isClassMethodParamLockedIn($functionLike, $position)) {
            return true;
        }

        if ($param->variadic) {
            return true;
        }

        // no type → check it
        if ($param->type === null) {
            return false;
        }

        // already set → skip
        return ! $param->type->getAttribute(NewType::HAS_NEW_INHERITED_TYPE, false);
    }

    private function isTraitType(Type $type): bool
    {
        if (! $type instanceof TypeWithClassName) {
            return false;
        }

        $fullyQualifiedName = $this->nodeTypeResolver->getFullyQualifiedClassName($type);
        $classReflection = $this->reflectionProvider->getClass($fullyQualifiedName);

        return $classReflection->isTrait();
    }
}
