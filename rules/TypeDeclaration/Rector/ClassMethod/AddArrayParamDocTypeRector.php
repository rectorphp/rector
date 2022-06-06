<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\TypeDeclaration\Rector\ClassMethod;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PHPStan\Type\ArrayType;
use RectorPrefix20220606\PHPStan\Type\CallableType;
use RectorPrefix20220606\PHPStan\Type\MixedType;
use RectorPrefix20220606\PHPStan\Type\NullType;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\PHPStan\Type\UnionType;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use RectorPrefix20220606\Rector\Core\NodeAnalyzer\ParamAnalyzer;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\DeadCode\PhpDoc\TagRemover\ParamTagRemover;
use RectorPrefix20220606\Rector\TypeDeclaration\TypeInferer\ParamTypeInferer;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector\AddArrayParamDocTypeRectorTest
 */
final class AddArrayParamDocTypeRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\ParamTypeInferer
     */
    private $paramTypeInferer;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\DeadCode\PhpDoc\TagRemover\ParamTagRemover
     */
    private $paramTagRemover;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ParamAnalyzer
     */
    private $paramAnalyzer;
    public function __construct(ParamTypeInferer $paramTypeInferer, PhpDocTypeChanger $phpDocTypeChanger, ParamTagRemover $paramTagRemover, ParamAnalyzer $paramAnalyzer)
    {
        $this->paramTypeInferer = $paramTypeInferer;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->paramTagRemover = $paramTagRemover;
        $this->paramAnalyzer = $paramAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Adds @param annotation to array parameters inferred from the rest of the code', [new CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
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
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->getParams() === []) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $hasChangedNode = \false;
        foreach ($node->getParams() as $param) {
            if ($this->shouldSkipParam($param)) {
                continue;
            }
            $paramType = $this->paramTypeInferer->inferParam($param);
            if ($paramType instanceof MixedType) {
                continue;
            }
            if ($paramType instanceof CallableType) {
                continue;
            }
            if ($this->paramAnalyzer->isNullable($param) && !$paramType instanceof UnionType) {
                $paramType = new UnionType([$paramType, new NullType()]);
            }
            $paramName = $this->getName($param);
            $this->phpDocTypeChanger->changeParamType($phpDocInfo, $paramType, $param, $paramName);
            $hasChangedNode = \true;
        }
        if ($phpDocInfo->hasChanged() && $hasChangedNode) {
            $this->paramTagRemover->removeParamTagsIfUseless($phpDocInfo, $node);
            return $node;
        }
        return null;
    }
    private function shouldSkipParam(Param $param) : bool
    {
        // type missing at all
        if ($param->type === null) {
            return \true;
        }
        // not an array type
        $paramType = $this->nodeTypeResolver->getType($param->type);
        // weird case for maybe interface
        if ($paramType->isIterable()->maybe() && $paramType instanceof ObjectType) {
            return \true;
        }
        $isArrayable = $paramType->isIterable()->yes() || $paramType->isArray()->yes() || ($paramType->isIterable()->maybe() || $paramType->isArray()->maybe());
        if (!$isArrayable) {
            return \true;
        }
        return $this->isArrayExplicitMixed($paramType);
    }
    private function isArrayExplicitMixed(Type $type) : bool
    {
        if (!$type instanceof ArrayType) {
            return \false;
        }
        $iterableValueType = $type->getIterableValueType();
        if (!$iterableValueType instanceof MixedType) {
            return \false;
        }
        return $iterableValueType->isExplicitMixed();
    }
}
