<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\ArrayDimFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Nette\DocBlock\VarAnnotationManipulator;
use Rector\Nette\Naming\NetteControlNaming;
use Rector\Nette\NodeAnalyzer\ControlDimFetchAnalyzer;
use Rector\Nette\NodeResolver\FormVariableInputNameTypeResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\Nette\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\ChangeFormArrayAccessToAnnotatedControlVariableRectorTest
 */
final class ChangeFormArrayAccessToAnnotatedControlVariableRector extends AbstractRector
{
    /**
     * @var FormVariableInputNameTypeResolver
     */
    private $formVariableInputNameTypeResolver;

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

    public function __construct(
        FormVariableInputNameTypeResolver $formVariableInputNameTypeResolver,
        ControlDimFetchAnalyzer $controlDimFetchAnalyzer,
        NetteControlNaming $netteControlNaming,
        VarAnnotationManipulator $varAnnotationManipulator
    ) {
        $this->formVariableInputNameTypeResolver = $formVariableInputNameTypeResolver;
        $this->controlDimFetchAnalyzer = $controlDimFetchAnalyzer;
        $this->netteControlNaming = $netteControlNaming;
        $this->varAnnotationManipulator = $varAnnotationManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change array access magic on $form to explicit standalone typed variable', [
            new CodeSample(
                <<<'PHP'
use Nette\Application\UI\Form;

class SomePresenter
{
    public function run()
    {
        $form = new Form();
        $this->addText('email', 'Email');

        $form['email']->value = 'hey@hi.hello';
    }
}
PHP
,
                <<<'PHP'
use Nette\Application\UI\Form;

class SomePresenter
{
    public function run()
    {
        $form = new Form();
        $this->addText('email', 'Email');

        /** @var \Nette\Forms\Controls\TextInput $emailControl */
        $emailControl = $form['email'];
        $emailControl->value = 'hey@hi.hello';
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
        $inputName = $this->controlDimFetchAnalyzer->matchName($node);
        if ($inputName === null) {
            return null;
        }

        if ($this->isBeingAssignedOrInitialized($node)) {
            return null;
        }

        $controlVariableName = $this->netteControlNaming->createVariableName($inputName);

        $controlVariableToFormDimFetchAssign = new Assign(new Variable($controlVariableName), clone $node);
        $assignExpression = new Expression($controlVariableToFormDimFetchAssign);

        // 1. find previous calls on variable
        /** @var Variable $formVariable */
        $formVariable = $node->var;

        $controlType = $this->formVariableInputNameTypeResolver->resolveControlTypeByInputName(
            $formVariable,
            $inputName
        );

        $formVariableName = $this->getName($formVariable);
        if ($formVariableName === null) {
            throw new ShouldNotHappenException();
        }

        $controlObjectType = new ObjectType($controlType);
        $this->varAnnotationManipulator->decorateNodeWithInlineVarType(
            $assignExpression,
            $controlObjectType,
            $controlVariableName
        );

        $this->addNodeBeforeNode($assignExpression, $node);

        return new Variable($controlVariableName);
    }

    private function isBeingAssignedOrInitialized(ArrayDimFetch $arrayDimFetch): bool
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
}
