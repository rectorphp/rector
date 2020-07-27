<?php

declare(strict_types=1);

namespace Rector\NetteCodeQuality\Rector\ArrayDimFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Variable;
use PHPStan\Type\ObjectType;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NetteCodeQuality\NodeResolver\MethodNamesByInputNamesResolver;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeControlArrayAccessToAnnotatedControlVariableRector\ChangeControlArrayAccessToAnnotatedControlVariableRectorTest
 */
final class ChangeControlArrayAccessToAnnotatedControlVariableRector extends AbstractArrayDimFetchToAnnotatedControlVariableRector
{
    /**
     * @var MethodNamesByInputNamesResolver
     */
    private $methodNamesByInputNamesResolver;

    public function __construct(MethodNamesByInputNamesResolver $methodNamesByInputNamesResolver)
    {
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
        if ($this->isBeingAssignedOrInitialized($node)) {
            return null;
        }

        $controlName = $this->controlDimFetchAnalyzer->matchNameOnControlVariable($node);
        if ($controlName === null) {
            return null;
        }

        $variableName = $this->netteControlNaming->createVariableName($controlName);

        $controlObjectType = $this->resolveControlType($node, $controlName);
        $this->addAssignExpressionForFirstCase($variableName, $node, $controlObjectType);

        return new Variable($variableName);
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
}
