<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\NodeAnalyzer\ParamAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\PhpDoc\TagRemover\ParamTagRemover;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\TypeInferer\ParamTypeInferer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector\AddArrayParamDocTypeRectorTest
 */
final class AddArrayParamDocTypeRector extends AbstractRector
{
    public function __construct(
        private ParamTypeInferer $paramTypeInferer,
        private PhpDocTypeChanger $phpDocTypeChanger,
        private ParamTagRemover $paramTagRemover,
        private ParamAnalyzer $paramAnalyzer
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Adds @param annotation to array parameters inferred from the rest of the code',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var int[]
     */
    private $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var int[]
     */
    private $values;

    /**
     * @param int[] $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->getParams() === []) {
            return null;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);

        foreach ($node->getParams() as $param) {
            if ($this->shouldSkipParam($param)) {
                continue;
            }

            $paramType = $this->paramTypeInferer->inferParam($param);
            if ($paramType instanceof MixedType) {
                continue;
            }

            $paramName = $this->getName($param);

            $this->phpDocTypeChanger->changeParamType($phpDocInfo, $paramType, $param, $paramName);
        }

        if ($phpDocInfo->hasChanged()) {
            $node->setAttribute(AttributeKey::HAS_PHP_DOC_INFO_JUST_CHANGED, true);
            $this->paramTagRemover->removeParamTagsIfUseless($phpDocInfo, $node);
            return $node;
        }

        return null;
    }

    private function shouldSkipParam(Param $param): bool
    {
        // type missing at all
        if ($param->type === null) {
            return true;
        }

        if ($this->paramAnalyzer->isNullable($param)) {
            return true;
        }

        // not an array type
        $paramType = $this->nodeTypeResolver->resolve($param->type);

        // weird case for maybe interface
        if ($paramType->isIterable()->maybe() && ($paramType instanceof ObjectType)) {
            return true;
        }

        $isArrayable = $paramType->isIterable()
            ->yes() || $paramType->isArray()
            ->yes() || ($paramType->isIterable()->maybe() || $paramType->isArray()->maybe());
        if (! $isArrayable) {
            return true;
        }

        return $this->isArrayExplicitMixed($paramType);
    }

    private function isArrayExplicitMixed(Type $type): bool
    {
        if (! $type instanceof ArrayType) {
            return false;
        }

        $iterableValueType = $type->getIterableValueType();
        if (! $iterableValueType instanceof MixedType) {
            return false;
        }

        return $iterableValueType->isExplicitMixed();
    }
}
