<?php

declare(strict_types=1);

namespace Rector\NetteCodeQuality\Rector\ArrayDimFetch;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NetteCodeQuality\DocBlock\VarAnnotationManipulator;
use Rector\NetteCodeQuality\Naming\NetteControlNaming;
use Rector\NetteCodeQuality\NodeAnalyzer\ControlDimFetchAnalyzer;
use Rector\NetteCodeQuality\NodeResolver\MethodNamesByInputNamesResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeControlArrayAccessToAnnotatedControlVariableRector\ChangeControlArrayAccessToAnnotatedControlVariableRectorTest
 */
final class ChangeControlArrayAccessToAnnotatedControlVariableRector extends AbstractRector
{
    /**
     * @var ControlDimFetchAnalyzer
     */
    private $controlDimFetchAnalyzer;

    /**
     * @var NetteControlNaming
     */
    private $netteControlNaming;

    /**
     * @var VarAnnotationManipulator
     */
    private $varAnnotationManipulator;

    /**
     * @var MethodNamesByInputNamesResolver
     */
    private $methodNamesByInputNamesResolver;

    /**
     * @var string[]
     */
    private $alreadyInitializedAssignsClassMethodObjectHashes = [];

    public function __construct(
        VarAnnotationManipulator $varAnnotationManipulator,
        ControlDimFetchAnalyzer $controlDimFetchAnalyzer,
        NetteControlNaming $netteControlNaming,
        MethodNamesByInputNamesResolver $methodNamesByInputNamesResolver
    ) {
        $this->controlDimFetchAnalyzer = $controlDimFetchAnalyzer;
        $this->netteControlNaming = $netteControlNaming;
        $this->varAnnotationManipulator = $varAnnotationManipulator;
        $this->methodNamesByInputNamesResolver = $methodNamesByInputNamesResolver;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change magic $this["some_component"] to variable assign with @var annotation', [
            new CodeSample(
                <<<'PHP'
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form;

final class SomePresenter extends Presenter
{
    public function run()
    {
        if ($this['some_form']->isSubmitted()) {
        }
    }

    protected function createComponentSomeForm()
    {
        return new Form();
    }
}
PHP
,
                <<<'PHP'
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form;

final class SomePresenter extends Presenter
{
    public function run()
    {
        /** @var \Nette\Application\UI\Form $someForm */
        $someForm = $this['some_form'];
        if ($someForm->isSubmitted()) {
        }
    }

    protected function createComponentSomeForm()
    {
        return new Form();
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
        return [ArrayDimFetch::class];
    }

    /**
     * @param ArrayDimFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->isBeingAssigned($node)) {
            return null;
        }

        $controlName = $this->controlDimFetchAnalyzer->matchName($node);
        if ($controlName === null) {
            return null;
        }

        $variableName = $this->netteControlNaming->createVariableName($controlName);

        $controlObjectType = $this->resolveControlType($node, $controlName);
        $this->addAssignExpressionForFirstCase($variableName, $node, $controlObjectType);

        return new Variable($variableName);
    }

    private function createAssignExpression(string $variableName, ArrayDimFetch $arrayDimFetch): Expression
    {
        $variable = new Variable($variableName);
        $assignedArrayDimFetch = clone $arrayDimFetch;
        $assign = new Assign($variable, $assignedArrayDimFetch);

        return new Expression($assign);
    }

    private function addAssignExpressionForFirstCase(
        string $variableName,
        ArrayDimFetch $arrayDimFetch,
        ObjectType $controlObjectType
    ): void {
        /** @var ClassMethod|null $classMethod */
        $classMethod = $arrayDimFetch->getAttribute(AttributeKey::METHOD_NODE);
        if ($classMethod !== null) {
            $classMethodObjectHash = spl_object_hash($classMethod) . $variableName;
            if (in_array($classMethodObjectHash, $this->alreadyInitializedAssignsClassMethodObjectHashes, true)) {
                return;
            }

            $this->alreadyInitializedAssignsClassMethodObjectHashes[] = $classMethodObjectHash;
        }

        $assignExpression = $this->createAssignExpression($variableName, $arrayDimFetch);

        $this->varAnnotationManipulator->decorateNodeWithInlineVarType(
            $assignExpression,
            $controlObjectType,
            $variableName
        );

        $currentStatement = $arrayDimFetch->getAttribute(AttributeKey::CURRENT_STATEMENT);
        $this->addNodeBeforeNode($assignExpression, $currentStatement);
    }

    private function resolveControlType(ArrayDimFetch $arrayDimFetch, string $controlName): ObjectType
    {
        $controlTypes = $this->methodNamesByInputNamesResolver->resolveExpr($arrayDimFetch);
        if ($controlTypes === []) {
            throw new NotImplementedYetException();
        }

        if (! isset($controlTypes[$controlName])) {
            throw new ShouldNotHappenException();
        }

        $controlType = $controlTypes[$controlName];

        return new ObjectType($controlType);
    }

    private function isBeingAssigned(Expr $expr): bool
    {
        $parent = $expr->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parent instanceof Assign) {
            return false;
        }

        return $parent->expr === $expr;
    }
}
