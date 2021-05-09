<?php

declare (strict_types=1);
namespace Rector\CodeQualityStrict\Rector\Stmt;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQualityStrict\Rector\Stmt\VarInlineAnnotationToAssertRector\VarInlineAnnotationToAssertRectorTest
 */
final class VarInlineAnnotationToAssertRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const ASSERT = 'assert';
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turn @var inline checks above code to assert() of the type', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        /** @var SpecificClass $value */
        $value->call();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        /** @var SpecificClass $value */
        assert($value instanceof SpecificClass);
        $value->call();
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
        return [\PhpParser\Node\Stmt::class];
    }
    /**
     * @param Stmt $node
     * @return Node|Node[]|null
     */
    public function refactor(\PhpParser\Node $node)
    {
        // skip properties
        if ($node instanceof \PhpParser\Node\Stmt\Property) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $docVariableName = $this->getVarDocVariableName($phpDocInfo);
        if ($docVariableName === null) {
            return null;
        }
        $variable = $this->findVariableByName($node, $docVariableName);
        if (!$variable instanceof \PhpParser\Node\Expr\Variable) {
            return null;
        }
        $isVariableJustCreated = $this->isVariableJustCreated($node, $docVariableName);
        if (!$isVariableJustCreated) {
            return $this->refactorFreshlyCreatedNode($node, $phpDocInfo, $variable);
        }
        return $this->refactorAlreadyCreatedNode($node, $phpDocInfo, $variable);
    }
    private function getVarDocVariableName(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo) : ?string
    {
        $varTagValueNode = $phpDocInfo->getVarTagValueNode();
        if (!$varTagValueNode instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode) {
            return null;
        }
        $variableName = $varTagValueNode->variableName;
        // no variable
        if ($variableName === '') {
            return null;
        }
        return \ltrim($variableName, '$');
    }
    private function findVariableByName(\PhpParser\Node\Stmt $stmt, string $docVariableName) : ?\PhpParser\Node
    {
        return $this->betterNodeFinder->findFirst($stmt, function (\PhpParser\Node $node) use($docVariableName) : bool {
            if (!$node instanceof \PhpParser\Node\Expr\Variable) {
                return \false;
            }
            return $this->nodeNameResolver->isName($node, $docVariableName);
        });
    }
    private function isVariableJustCreated(\PhpParser\Node\Stmt $stmt, string $docVariableName) : bool
    {
        if (!$stmt instanceof \PhpParser\Node\Stmt\Expression) {
            return \false;
        }
        if (!$stmt->expr instanceof \PhpParser\Node\Expr\Assign) {
            return \false;
        }
        $assign = $stmt->expr;
        if (!$assign->var instanceof \PhpParser\Node\Expr\Variable) {
            return \false;
        }
        // the variable is on the left side = just created
        return $this->nodeNameResolver->isName($assign->var, $docVariableName);
    }
    private function refactorFreshlyCreatedNode(\PhpParser\Node\Stmt $stmt, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, \PhpParser\Node\Expr\Variable $variable) : ?\PhpParser\Node
    {
        $stmt->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS, null);
        $type = $phpDocInfo->getVarType();
        $assertFuncCall = $this->createFuncCallBasedOnType($type, $variable);
        if (!$assertFuncCall instanceof \PhpParser\Node\Expr\FuncCall) {
            return null;
        }
        $phpDocInfo->removeByType(\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode::class);
        $this->addNodeBeforeNode($assertFuncCall, $stmt);
        return $stmt;
    }
    /**
     * @return Node[]|null
     */
    private function refactorAlreadyCreatedNode(\PhpParser\Node\Stmt $stmt, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, \PhpParser\Node\Expr\Variable $variable) : ?array
    {
        $varTagValue = $phpDocInfo->getVarTagValueNode();
        if (!$varTagValue instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $phpStanType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($varTagValue->type, $variable);
        $assertFuncCall = $this->createFuncCallBasedOnType($phpStanType, $variable);
        if (!$assertFuncCall instanceof \PhpParser\Node\Expr\FuncCall) {
            return null;
        }
        return [$stmt, new \PhpParser\Node\Stmt\Expression($assertFuncCall)];
    }
    private function createFuncCallBasedOnType(\PHPStan\Type\Type $type, \PhpParser\Node\Expr\Variable $variable) : ?\PhpParser\Node\Expr\FuncCall
    {
        if ($type instanceof \PHPStan\Type\ObjectType) {
            $instanceOf = new \PhpParser\Node\Expr\Instanceof_($variable, new \PhpParser\Node\Name\FullyQualified($type->getClassName()));
            return $this->nodeFactory->createFuncCall(self::ASSERT, [$instanceOf]);
        }
        if ($type instanceof \PHPStan\Type\IntegerType) {
            $isInt = $this->nodeFactory->createFuncCall('is_int', [$variable]);
            return $this->nodeFactory->createFuncCall(self::ASSERT, [$isInt]);
        }
        if ($type instanceof \PHPStan\Type\FloatType) {
            $funcCall = $this->nodeFactory->createFuncCall('is_float', [$variable]);
            return $this->nodeFactory->createFuncCall(self::ASSERT, [$funcCall]);
        }
        if ($type instanceof \PHPStan\Type\StringType) {
            $isString = $this->nodeFactory->createFuncCall('is_string', [$variable]);
            return $this->nodeFactory->createFuncCall(self::ASSERT, [$isString]);
        }
        if ($type instanceof \PHPStan\Type\BooleanType) {
            $isInt = $this->nodeFactory->createFuncCall('is_bool', [$variable]);
            return $this->nodeFactory->createFuncCall(self::ASSERT, [$isInt]);
        }
        return null;
    }
}
