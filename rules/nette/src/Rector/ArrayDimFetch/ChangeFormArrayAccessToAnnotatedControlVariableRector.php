<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\ArrayDimFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareFullyQualifiedIdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\Util\StaticRectorStrings;
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

    public function __construct(FormVariableInputNameTypeResolver $formVariableInputNameTypeResolver)
    {
        $this->formVariableInputNameTypeResolver = $formVariableInputNameTypeResolver;
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
        $dimString = $this->matchNetteFormArrayDimString($node);
        if ($dimString === null) {
            return null;
        }

        if ($this->isBeingAssignedOrInitialized($node)) {
            return null;
        }

        $inputName = $this->getValue($dimString);
        $controlVariableName = $this->createControlVariableName($inputName);

        $controlVariableToFormDimFetchAssign = new Assign(new Variable($controlVariableName), clone $node);
        $assignExpression = new Expression($controlVariableToFormDimFetchAssign);

        // 1. find previous calls on variable
        /** @var Variable $formVariable */
        $formVariable = $node->var;

        $controlType = $this->formVariableInputNameTypeResolver->resolveControlTypeByInputName(
            $formVariable,
            $inputName
        );

        $this->addVarTag($controlVariableToFormDimFetchAssign, $assignExpression, $controlVariableName, $controlType);

        $this->addNodeBeforeNode($assignExpression, $node);

        return new Variable($controlVariableName);
    }

    private function addVarTag(
        Assign $assign,
        Expression $assignExpression,
        string $controlName,
        string $controlType
    ): PhpDocInfo {
        $phpDocInfo = $this->phpDocInfoFactory->createEmpty($assignExpression);

        $varTagValueNode = new VarTagValueNode(
            new AttributeAwareFullyQualifiedIdentifierTypeNode($controlType),
            '$' . $controlName,
            ''
        );

        $phpDocInfo->addTagValueNode($varTagValueNode);
        $phpDocInfo->makeSingleLined();

        $assign->setAttribute(AttributeKey::PHP_DOC_INFO, $phpDocInfo);

        return $phpDocInfo;
    }

    private function matchNetteFormArrayDimString(ArrayDimFetch $arrayDimFetch): ?String_
    {
        if (! $arrayDimFetch->var instanceof Variable) {
            return null;
        }

        if (! $this->isObjectTypeOrNullableObjectType($arrayDimFetch->var, 'Nette\ComponentModel\IComponent')) {
            return null;
        }

        if (! $arrayDimFetch->dim instanceof String_) {
            return null;
        }

        return $arrayDimFetch->dim;
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

    private function createControlVariableName(string $inputName): string
    {
        return StaticRectorStrings::underscoreToPascalCase($inputName) . 'Control';
    }
}
