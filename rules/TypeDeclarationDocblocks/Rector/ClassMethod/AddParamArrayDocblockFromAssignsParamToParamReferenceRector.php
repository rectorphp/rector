<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclarationDocblocks\NodeDocblockTypeDecorator;
use Rector\TypeDeclarationDocblocks\NodeFinder\ArrayDimFetchFinder;
use Rector\TypeDeclarationDocblocks\TagNodeAnalyzer\UsefulArrayTagNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclarationDocblocks\Rector\ClassMethod\AddParamArrayDocblockFromAssignsParamToParamReferenceRector\AddParamArrayDocblockFromAssignsParamToParamReferenceRectorTest
 */
final class AddParamArrayDocblockFromAssignsParamToParamReferenceRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private ArrayDimFetchFinder $arrayDimFetchFinder;
    /**
     * @readonly
     */
    private UsefulArrayTagNodeAnalyzer $usefulArrayTagNodeAnalyzer;
    /**
     * @readonly
     */
    private NodeDocblockTypeDecorator $nodeDocblockTypeDecorator;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, ArrayDimFetchFinder $arrayDimFetchFinder, UsefulArrayTagNodeAnalyzer $usefulArrayTagNodeAnalyzer, NodeDocblockTypeDecorator $nodeDocblockTypeDecorator)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->arrayDimFetchFinder = $arrayDimFetchFinder;
        $this->usefulArrayTagNodeAnalyzer = $usefulArrayTagNodeAnalyzer;
        $this->nodeDocblockTypeDecorator = $nodeDocblockTypeDecorator;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add @param docblock array type, based on type to assigned parameter reference', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(array &$names): void
    {
        $names[] = 'John';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @param string[] $names
     */
    public function run(array &$names): void
    {
        $names[] = 'John';
    }
}
CODE_SAMPLE
)]);
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
        $hasChanged = \false;
        if ($node->getParams() === []) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        foreach ($node->getParams() as $param) {
            if (!$param->byRef) {
                continue;
            }
            if (!$param->type instanceof Identifier) {
                continue;
            }
            if (!$this->isName($param->type, 'array')) {
                continue;
            }
            $paramName = $this->getName($param);
            $paramTagValueNode = $phpDocInfo->getParamTagValueByName($paramName);
            // already defined, lets skip it
            if ($this->usefulArrayTagNodeAnalyzer->isUsefulArrayTag($paramTagValueNode)) {
                continue;
            }
            $exprs = $this->arrayDimFetchFinder->findDimFetchAssignToVariableName($node, $paramName);
            // to kick off with one
            if (count($exprs) !== 1) {
                continue;
            }
            $assignedExprType = $this->getType($exprs[0]);
            $iterableType = new ArrayType(new MixedType(), $assignedExprType);
            $hasParamTypeChanged = $this->nodeDocblockTypeDecorator->decorateGenericIterableParamType($iterableType, $phpDocInfo, $node, $param, $paramName);
            if (!$hasParamTypeChanged) {
                continue;
            }
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
}
