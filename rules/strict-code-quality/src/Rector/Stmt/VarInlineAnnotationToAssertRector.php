<?php

declare(strict_types=1);

namespace Rector\StrictCodeQuality\Rector\Stmt;

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
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\StrictCodeQuality\Tests\Rector\Stmt\VarInlineAnnotationToAssertRector\VarInlineAnnotationToAssertRectorTest
 */
final class VarInlineAnnotationToAssertRector extends AbstractRector
{
    /**
     * @var string
     */
    private const ASSERT = 'assert';

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turn @var inline checks above code to assert() of hte type', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        /** @var SpecificClass $value */
        $value->call();
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        /** @var SpecificClass $value */
        assert($value instanceof SpecificClass);
        $value->call();
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Stmt::class];
    }

    /**
     * @param Stmt $node
     */
    public function refactor(Node $node): ?Node
    {
        // skip properties
        if ($node instanceof Property) {
            return null;
        }

        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return null;
        }

        $docVariableName = $this->getVarDocVariableName($phpDocInfo);
        if ($docVariableName === null) {
            return null;
        }

        $variable = $this->findVariableByName($node, $docVariableName);
        if (! $variable instanceof Variable) {
            return null;
        }

        $isVariableJustCreated = $this->isVariableJustCreated($node, $docVariableName);
        if (! $isVariableJustCreated) {
            return $this->refactorFreshlyCreatedNode($node, $phpDocInfo, $variable);
        }

        return $this->refactorAlreadyCreatedNode($node, $phpDocInfo, $variable);
    }

    private function getVarDocVariableName(PhpDocInfo $phpDocInfo): ?string
    {
        $attributeAwareVarTagValueNode = $phpDocInfo->getVarTagValue();
        if ($attributeAwareVarTagValueNode === null) {
            return null;
        }

        $variableName = (string) $attributeAwareVarTagValueNode->variableName;
        // no variable
        if ($variableName === '') {
            return null;
        }

        return ltrim($variableName, '$');
    }

    private function findVariableByName(Stmt $stmt, string $docVariableName): ?Node
    {
        return $this->betterNodeFinder->findFirst($stmt, function (Node $stmt) use ($docVariableName): bool {
            return $this->isVariableName($stmt, $docVariableName);
        });
    }

    private function isVariableJustCreated(Node $node, string $docVariableName): bool
    {
        if (! $node instanceof Expression) {
            return false;
        }

        if (! $node->expr instanceof Assign) {
            return false;
        }

        $assign = $node->expr;

        // the variable is on the left side = just created
        return $this->isVariableName($assign->var, $docVariableName);
    }

    private function refactorFreshlyCreatedNode(Node $node, PhpDocInfo $phpDocInfo, Variable $variable): ?Node
    {
        $node->setAttribute(AttributeKey::COMMENTS, null);
        $type = $phpDocInfo->getVarType();

        $assertFuncCall = $this->createFuncCallBasedOnType($type, $variable);
        if ($assertFuncCall === null) {
            return null;
        }

        $phpDocInfo->removeByType(VarTagValueNode::class);

        $this->addNodeBeforeNode($assertFuncCall, $node);

        return $node;
    }

    private function refactorAlreadyCreatedNode(Node $node, PhpDocInfo $phpDocInfo, Variable $variable): ?Node
    {
        $varTagValue = $phpDocInfo->getVarTagValue();
        $phpStanType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType(
            $varTagValue->type,
            $variable
        );

        $assertFuncCall = $this->createFuncCallBasedOnType($phpStanType, $variable);
        if ($assertFuncCall === null) {
            return null;
        }

        $this->addNodeAfterNode($assertFuncCall, $node);

        return $node;
    }

    private function createFuncCallBasedOnType(Type $type, Variable $variable): ?FuncCall
    {
        if ($type instanceof ObjectType) {
            $instanceOf = new Instanceof_($variable, new FullyQualified($type->getClassName()));
            return $this->createFuncCall(self::ASSERT, [$instanceOf]);
        }

        if ($type instanceof IntegerType) {
            $isInt = $this->createFuncCall('is_int', [$variable]);
            return $this->createFuncCall(self::ASSERT, [$isInt]);
        }

        if ($type instanceof FloatType) {
            $isFloat = $this->createFuncCall('is_float', [$variable]);
            return $this->createFuncCall(self::ASSERT, [$isFloat]);
        }

        if ($type instanceof StringType) {
            $isString = $this->createFuncCall('is_string', [$variable]);
            return $this->createFuncCall(self::ASSERT, [$isString]);
        }

        if ($type instanceof BooleanType) {
            $isInt = $this->createFuncCall('is_bool', [$variable]);
            return $this->createFuncCall(self::ASSERT, [$isInt]);
        }

        return null;
    }
}
