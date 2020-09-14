<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Nette\NodeFactory\ActionRenderFactory;
use Rector\Nette\TemplatePropertyAssignCollector;
use Rector\Nette\ValueObject\MagicTemplatePropertyCalls;

/**
 * @see \Rector\Nette\Tests\Rector\ClassMethod\TemplateMagicAssignToExplicitVariableArrayRector\TemplateMagicAssignToExplicitVariableArrayRectorTest
 */
final class TemplateMagicAssignToExplicitVariableArrayRector extends AbstractRector
{
    /**
     * @var TemplatePropertyAssignCollector
     */
    private $templatePropertyAssignCollector;

    /**
     * @var ActionRenderFactory
     */
    private $actionRenderFactory;

    public function __construct(
        ActionRenderFactory $actionRenderFactory,
        TemplatePropertyAssignCollector $templatePropertyAssignCollector
    ) {
        $this->templatePropertyAssignCollector = $templatePropertyAssignCollector;
        $this->actionRenderFactory = $actionRenderFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change `$this->templates->{magic}` to `$this->template->render(..., $values)`', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Nette\Application\UI\Control;

class SomeControl extends Control
{
    public function render()
    {
        $this->template->param = 'some value';
        $this->template->render(__DIR__ . '/poll.latte');
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
use Nette\Application\UI\Control;

class SomeControl extends Control
{
    public function render()
    {
        $this->template->render(__DIR__ . '/poll.latte', ['param' => 'some value']);
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isObjectType($node, 'Nette\Application\UI\Control')) {
            return null;
        }

        if (! $node->isPublic()) {
            return null;
        }

        $magicTemplatePropertyCalls = $this->templatePropertyAssignCollector->collectTemplateFileNameVariablesAndNodesToRemove(
            $node
        );

        $renderMethodCall = $this->createRenderMethodCall($node, $magicTemplatePropertyCalls);
        $node->stmts = array_merge((array) $node->stmts, [new Expression($renderMethodCall)]);

        $this->removeNodes($magicTemplatePropertyCalls->getNodesToRemove());

        return $node;
    }

    private function createRenderMethodCall(
        ClassMethod $classMethod,
        MagicTemplatePropertyCalls $magicTemplatePropertyCalls
    ): MethodCall {
        if ($this->isObjectType($classMethod, 'Nette\Application\UI\Presenter')) {
            return $this->actionRenderFactory->createThisTemplateRenderMethodCall($magicTemplatePropertyCalls);
        }

        return $this->actionRenderFactory->createThisRenderMethodCall($magicTemplatePropertyCalls);
    }
}
