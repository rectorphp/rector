<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclarationDocblocks\NodeFinder\ReturnNodeFinder;
use Rector\TypeDeclarationDocblocks\TagNodeAnalyzer\UsefulArrayTagNodeAnalyzer;
use Rector\TypeDeclarationDocblocks\TypeResolver\ConstantArrayTypeGeneralizer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclarationDocblocks\Rector\ClassMethod\AddReturnDocblockForDimFetchArrayFromAssignsRector\AddReturnDocblockForDimFetchArrayFromAssignsRectorTest
 */
final class AddReturnDocblockForDimFetchArrayFromAssignsRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private UsefulArrayTagNodeAnalyzer $usefulArrayTagNodeAnalyzer;
    /**
     * @readonly
     */
    private ReturnNodeFinder $returnNodeFinder;
    /**
     * @readonly
     */
    private ConstantArrayTypeGeneralizer $constantArrayTypeGeneralizer;
    /**
     * @readonly
     */
    private PhpDocTypeChanger $phpDocTypeChanger;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, UsefulArrayTagNodeAnalyzer $usefulArrayTagNodeAnalyzer, ReturnNodeFinder $returnNodeFinder, ConstantArrayTypeGeneralizer $constantArrayTypeGeneralizer, PhpDocTypeChanger $phpDocTypeChanger)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->usefulArrayTagNodeAnalyzer = $usefulArrayTagNodeAnalyzer;
        $this->returnNodeFinder = $returnNodeFinder;
        $this->constantArrayTypeGeneralizer = $constantArrayTypeGeneralizer;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add @return docblock for methods returning array from dim fetch of assigned arrays', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function toArray(): array
    {
        $items = [];

        if (mt_rand(0, 1)) {
            $items['key'] = 'value';
        }

        if (mt_rand(0, 1)) {
            $items['another_key'] = 'another_value';
        }

        return $items;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @return array<string, string>
     */
    public function toArray()
    {
        $items = [];

        if (mt_rand(0, 1)) {
            $items['key'] = 'value';
        }

        if (mt_rand(0, 1)) {
            $items['another_key'] = 'another_value';
        }

        return $items;
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?ClassMethod
    {
        if ($node->stmts === null) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        if ($this->usefulArrayTagNodeAnalyzer->isUsefulArrayTag($phpDocInfo->getReturnTagValue())) {
            return null;
        }
        $soleReturn = $this->returnNodeFinder->findOnlyReturnWithExpr($node);
        if (!$soleReturn instanceof Return_) {
            return null;
        }
        // only variable
        if (!$soleReturn->expr instanceof Variable) {
            return null;
        }
        // @todo check type here
        $returnedExprType = $this->getType($soleReturn->expr);
        if (!$this->isConstantArrayType($returnedExprType)) {
            return null;
        }
        // find stmts with $item = [];
        $returnedVariableName = $this->getName($soleReturn->expr);
        if (!is_string($returnedVariableName)) {
            return null;
        }
        if (!$this->isVariableInstantiated($node, $returnedVariableName)) {
            return null;
        }
        if ($returnedExprType->getReferencedClasses() !== []) {
            // better handled by shared-interface/class rule, to avoid turning objects to mixed
            return null;
        }
        // conditional assign
        $genericUnionedTypeNodes = [];
        if ($returnedExprType instanceof UnionType) {
            foreach ($returnedExprType->getTypes() as $unionedType) {
                if ($unionedType instanceof ConstantArrayType) {
                    // skip empty array
                    if ($unionedType->getKeyTypes() === [] && $unionedType->getValueTypes() === []) {
                        continue;
                    }
                    $genericUnionedTypeNode = $this->constantArrayTypeGeneralizer->generalize($unionedType);
                    $genericUnionedTypeNodes[] = $genericUnionedTypeNode;
                }
            }
        } else {
            /** @var ConstantArrayType $returnedExprType */
            $genericTypeNode = $this->constantArrayTypeGeneralizer->generalize($returnedExprType);
            $this->phpDocTypeChanger->changeReturnTypeNode($node, $phpDocInfo, $genericTypeNode);
            return $node;
        }
        // @todo handle multiple type nodes
        $this->phpDocTypeChanger->changeReturnTypeNode($node, $phpDocInfo, $genericUnionedTypeNodes[0]);
        return $node;
    }
    private function isVariableInstantiated(ClassMethod $classMethod, string $returnedVariableName): bool
    {
        foreach ((array) $classMethod->stmts as $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if (!$stmt->expr instanceof Assign) {
                continue;
            }
            $assign = $stmt->expr;
            if (!$assign->var instanceof Variable) {
                continue;
            }
            if (!$this->isName($assign->var, $returnedVariableName)) {
                continue;
            }
            // must be array assignment
            if (!$assign->expr instanceof Array_) {
                continue;
            }
            return \true;
        }
        return \false;
    }
    private function isConstantArrayType(Type $returnedExprType): bool
    {
        if ($returnedExprType instanceof UnionType) {
            foreach ($returnedExprType->getTypes() as $unionedType) {
                if (!$unionedType instanceof ConstantArrayType) {
                    return \false;
                }
            }
            return \true;
        }
        return $returnedExprType instanceof ConstantArrayType;
    }
}
