<?php

declare(strict_types=1);

namespace Rector\NetteCodeQuality\Rector\ArrayDimFetch;

use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocManipulator\VarAnnotationManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\NetteCodeQuality\Naming\NetteControlNaming;
use Rector\NetteCodeQuality\NodeAdding\FunctionLikeFirstLevelStatementResolver;
use Rector\NetteCodeQuality\NodeAnalyzer\ControlDimFetchAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 */
abstract class AbstractArrayDimFetchToAnnotatedControlVariableRector extends AbstractRector
{
    /**
     * @var ControlDimFetchAnalyzer
     */
    protected $controlDimFetchAnalyzer;

    /**
     * @var NetteControlNaming
     */
    protected $netteControlNaming;

    /**
     * @var VarAnnotationManipulator
     */
    protected $varAnnotationManipulator;

    /**
     * @var string[]
     */
    private $alreadyInitializedAssignsClassMethodObjectHashes = [];

    /**
     * @var FunctionLikeFirstLevelStatementResolver
     */
    private $functionLikeFirstLevelStatementResolver;

    /**
     * @required
     */
    public function autowireAbstractArrayDimFetchToAnnotatedControlVariableRector(
        VarAnnotationManipulator $varAnnotationManipulator,
        ControlDimFetchAnalyzer $controlDimFetchAnalyzer,
        NetteControlNaming $netteControlNaming,
        FunctionLikeFirstLevelStatementResolver $functionLikeFirstLevelStatementResolver
    ): void {
        $this->controlDimFetchAnalyzer = $controlDimFetchAnalyzer;
        $this->netteControlNaming = $netteControlNaming;
        $this->varAnnotationManipulator = $varAnnotationManipulator;
        $this->functionLikeFirstLevelStatementResolver = $functionLikeFirstLevelStatementResolver;
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ArrayDimFetch::class];
    }

    protected function addAssignExpressionForFirstCase(
        string $variableName,
        ArrayDimFetch $arrayDimFetch,
        ObjectType $controlObjectType
    ): void {
        if ($this->shouldSkipForAlreadyAddedInCurrentClassMethod($arrayDimFetch, $variableName)) {
            return;
        }

        $assignExpression = $this->createAnnotatedAssignExpression($variableName, $arrayDimFetch, $controlObjectType);

        $currentStatement = $this->functionLikeFirstLevelStatementResolver->resolveFirstLevelStatement($arrayDimFetch);
        $this->addNodeBeforeNode($assignExpression, $currentStatement);
    }

    protected function isBeingAssignedOrInitialized(ArrayDimFetch $arrayDimFetch): bool
    {
        $parent = $arrayDimFetch->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parent instanceof Assign) {
            return false;
        }

        if ($parent->var === $arrayDimFetch) {
            return true;
        }

        return $parent->expr === $arrayDimFetch;
    }

    private function shouldSkipForAlreadyAddedInCurrentClassMethod(
        ArrayDimFetch $arrayDimFetch,
        string $variableName
    ): bool {
        $classMethod = $arrayDimFetch->getAttribute(AttributeKey::METHOD_NODE);

        if (! $classMethod instanceof ClassMethod) {
            return false;
        }

        $classMethodObjectHash = spl_object_hash($classMethod) . $variableName;
        if (in_array($classMethodObjectHash, $this->alreadyInitializedAssignsClassMethodObjectHashes, true)) {
            return true;
        }

        $this->alreadyInitializedAssignsClassMethodObjectHashes[] = $classMethodObjectHash;

        return false;
    }

    private function createAnnotatedAssignExpression(
        string $variableName,
        ArrayDimFetch $arrayDimFetch,
        ObjectType $controlObjectType
    ): Expression {
        $assignExpression = $this->createAssignExpression($variableName, $arrayDimFetch);

        $this->varAnnotationManipulator->decorateNodeWithInlineVarType(
            $assignExpression,
            $controlObjectType,
            $variableName
        );

        return $assignExpression;
    }

    private function createAssignExpression(string $variableName, ArrayDimFetch $arrayDimFetch): Expression
    {
        $variable = new Variable($variableName);
        $assignedArrayDimFetch = clone $arrayDimFetch;
        $assign = new Assign($variable, $assignedArrayDimFetch);

        return new Expression($assign);
    }
}
