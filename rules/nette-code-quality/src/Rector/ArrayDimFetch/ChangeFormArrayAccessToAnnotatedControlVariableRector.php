<?php

declare(strict_types=1);

namespace Rector\NetteCodeQuality\Rector\ArrayDimFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Unset_;
use PHPStan\Type\ObjectType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NetteCodeQuality\NodeResolver\FormVariableInputNameTypeResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\PackageBuilder\Php\TypeChecker;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\ChangeFormArrayAccessToAnnotatedControlVariableRectorTest
 */
final class ChangeFormArrayAccessToAnnotatedControlVariableRector extends AbstractArrayDimFetchToAnnotatedControlVariableRector
{
    /**
     * @var FormVariableInputNameTypeResolver
     */
    private $formVariableInputNameTypeResolver;

    /**
     * @var TypeChecker
     */
    private $typeChecker;

    public function __construct(
        FormVariableInputNameTypeResolver $formVariableInputNameTypeResolver,
        TypeChecker $typeChecker
    ) {
        $this->formVariableInputNameTypeResolver = $formVariableInputNameTypeResolver;
        $this->typeChecker = $typeChecker;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change array access magic on $form to explicit standalone typed variable',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
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
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
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
CODE_SAMPLE
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
        if ($this->isBeingAssignedOrInitialized($node)) {
            return null;
        }

        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($this->typeChecker->isInstanceOf($parent, [Isset_::class, Unset_::class])) {
            return null;
        }

        $inputName = $this->controlDimFetchAnalyzer->matchNameOnFormOrControlVariable($node);
        if ($inputName === null) {
            return null;
        }

        $formVariableName = $this->getName($node->var);
        if ($formVariableName === null) {
            throw new ShouldNotHappenException();
        }

        // 1. find previous calls on variable
        $controlType = $this->formVariableInputNameTypeResolver->resolveControlTypeByInputName(
            $node->var,
            $inputName
        );

        $controlVariableName = $this->netteControlNaming->createVariableName($inputName);
        $controlObjectType = new ObjectType($controlType);
        $this->addAssignExpressionForFirstCase($controlVariableName, $node, $controlObjectType);

        return new Variable($controlVariableName);
    }
}
