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
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\StrictCodeQuality\Tests\Rector\Stmt\VarInlineAnnotationToAssertRector\VarInlineAnnotationToAssertRectorTest
 */
final class VarInlineAnnotationToAssertRector extends AbstractRector
{
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

        $phpDocInfo = $this->getPhpDocInfo($node);
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
        $varTagValueNode = $phpDocInfo->getVarTagValue();
        if ($varTagValueNode === null) {
            return null;
        }

        $variableName = $varTagValueNode->variableName;
        // no variable
        if (empty($variableName)) {
            return null;
        }

        return ltrim($variableName, '$');
    }

    private function findVariableByName(Stmt $stmt, string $docVariableName): ?Variable
    {
        return $this->betterNodeFinder->findFirst($stmt, function (Node $stmt) use ($docVariableName): bool {
            if (! $stmt instanceof Variable) {
                return false;
            }

            return $this->isName($stmt, $docVariableName);
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
        if (! $assign->var instanceof Variable) {
            return false;
        }

        return $this->isName($assign->var, $docVariableName);
    }

    private function refactorFreshlyCreatedNode(Node $node, PhpDocInfo $phpDocInfo, Variable $variable): ?Node
    {
        $node->setAttribute('comments', null);
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
            return $this->createFunction('assert', [$instanceOf]);
        }

        if ($type instanceof IntegerType) {
            $isInt = $this->createFunction('is_int', [$variable]);
            return $this->createFunction('assert', [$isInt]);
        }

        if ($type instanceof FloatType) {
            $isFloat = $this->createFunction('is_float', [$variable]);
            return $this->createFunction('assert', [$isFloat]);
        }

        if ($type instanceof StringType) {
            $isString = $this->createFunction('is_string', [$variable]);
            return $this->createFunction('assert', [$isString]);
        }

        if ($type instanceof BooleanType) {
            $isInt = $this->createFunction('is_bool', [$variable]);
            return $this->createFunction('assert', [$isInt]);
        }

        return null;
    }
}
