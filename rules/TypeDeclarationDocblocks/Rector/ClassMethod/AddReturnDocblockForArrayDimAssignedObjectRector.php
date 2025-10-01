<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeVisitor;
use PHPStan\Type\Accessory\AccessoryArrayListType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\ValueObject\Type\NonExistingObjectType;
use Rector\TypeDeclarationDocblocks\NodeDocblockTypeDecorator;
use Rector\TypeDeclarationDocblocks\NodeFinder\ReturnNodeFinder;
use Rector\TypeDeclarationDocblocks\TagNodeAnalyzer\UsefulArrayTagNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclarationDocblocks\Rector\ClassMethod\AddReturnDocblockForArrayDimAssignedObjectRector\AddReturnDocblockForArrayDimAssignedObjectRectorTest
 */
final class AddReturnDocblockForArrayDimAssignedObjectRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private ReturnNodeFinder $returnNodeFinder;
    /**
     * @readonly
     */
    private UsefulArrayTagNodeAnalyzer $usefulArrayTagNodeAnalyzer;
    /**
     * @readonly
     */
    private NodeDocblockTypeDecorator $nodeDocblockTypeDecorator;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, ReturnNodeFinder $returnNodeFinder, UsefulArrayTagNodeAnalyzer $usefulArrayTagNodeAnalyzer, NodeDocblockTypeDecorator $nodeDocblockTypeDecorator)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->returnNodeFinder = $returnNodeFinder;
        $this->usefulArrayTagNodeAnalyzer = $usefulArrayTagNodeAnalyzer;
        $this->nodeDocblockTypeDecorator = $nodeDocblockTypeDecorator;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add @return docblock array of objects, that are dim assigned to returned variable', [new CodeSample(<<<'CODE_SAMPLE'
final class ItemProvider
{
    public function provide(array $input): array
    {
        $items = [];

        foreach ($input as $value) {
            $items[] = new Item($value);
        }

        return $items;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class ItemProvider
{
    /**
     * @return Item[]
     */
    public function provide(array $input): array
    {
        $items = [];

        foreach ($input as $value) {
            $items[] = new Item($value);
        }

        return $items;
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
        return [ClassMethod::class, Function_::class];
    }
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $returnType = $phpDocInfo->getReturnType();
        if ($returnType instanceof ArrayType && !$returnType->getItemType() instanceof MixedType) {
            return null;
        }
        if ($this->usefulArrayTagNodeAnalyzer->isUsefulArrayTag($phpDocInfo->getReturnTagValue())) {
            return null;
        }
        // definitely not an array return
        if ($node->returnType instanceof Node && !$this->isName($node->returnType, 'array')) {
            return null;
        }
        $onlyReturnWithExpr = $this->returnNodeFinder->findOnlyReturnWithExpr($node);
        if (!$onlyReturnWithExpr instanceof Return_ || !$onlyReturnWithExpr->expr instanceof Variable) {
            return null;
        }
        // is expr only used to array dim assign?
        $returnedType = $this->getType($onlyReturnWithExpr->expr);
        $returnedVariableName = $this->getName($onlyReturnWithExpr->expr);
        if (!is_string($returnedVariableName)) {
            return null;
        }
        if ($this->isVariableExclusivelyArrayDimAssigned($node, $returnedVariableName) === \false) {
            return null;
        }
        $arrayObjectType = $this->matchArrayObjectType($returnedType);
        if (!$arrayObjectType instanceof ObjectType) {
            return null;
        }
        $objectTypeArrayType = new ArrayType(new MixedType(), $arrayObjectType);
        if (!$this->nodeDocblockTypeDecorator->decorateGenericIterableReturnType($objectTypeArrayType, $phpDocInfo, $node)) {
            return null;
        }
        return $node;
    }
    private function matchArrayObjectType(Type $returnedType): ?Type
    {
        if ($returnedType instanceof IntersectionType) {
            foreach ($returnedType->getTypes() as $intersectionedType) {
                if ($intersectionedType instanceof AccessoryArrayListType) {
                    continue;
                }
                if ($intersectionedType instanceof ArrayType && $intersectionedType->getItemType() instanceof ObjectType) {
                    return $intersectionedType->getItemType();
                }
                return null;
            }
        }
        return null;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    private function isVariableExclusivelyArrayDimAssigned($functionLike, string $variableName): bool
    {
        $isVariableExclusivelyArrayDimAssigned = \true;
        $this->traverseNodesWithCallable((array) $functionLike->stmts, function ($node) use ($variableName, &$isVariableExclusivelyArrayDimAssigned): ?int {
            if ($node instanceof Assign) {
                if ($node->var instanceof ArrayDimFetch) {
                    $arrayDimFetch = $node->var;
                    if (!$arrayDimFetch->var instanceof Variable) {
                        $isVariableExclusivelyArrayDimAssigned = \false;
                        return null;
                    }
                    if ($this->isName($arrayDimFetch->var, $variableName)) {
                        if ($arrayDimFetch->dim instanceof Expr) {
                            $isVariableExclusivelyArrayDimAssigned = \false;
                        }
                        $assignedType = $this->getType($node->expr);
                        if (!$assignedType instanceof ObjectType) {
                            $isVariableExclusivelyArrayDimAssigned = \false;
                        }
                        if ($assignedType instanceof NonExistingObjectType) {
                            $isVariableExclusivelyArrayDimAssigned = \false;
                        }
                        // ignore lower value
                        return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
                    }
                }
                if ($node->var instanceof Variable && $this->isName($node->var, $variableName) && $node->expr instanceof Array_) {
                    if ($node->expr->items === []) {
                        // ignore empty array assignment
                        return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
                    }
                    $isVariableExclusivelyArrayDimAssigned = \false;
                }
            }
            if ($node instanceof Return_ && $node->expr instanceof Variable) {
                if ($this->isName($node->expr, $variableName)) {
                    // ignore lower value
                    return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
                }
                $isVariableExclusivelyArrayDimAssigned = \false;
            }
            if ($node instanceof Variable && $this->isName($node, $variableName)) {
                $isVariableExclusivelyArrayDimAssigned = \false;
            }
            return null;
        });
        return $isVariableExclusivelyArrayDimAssigned;
    }
}
