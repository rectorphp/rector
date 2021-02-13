<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\Nette\NodeAnalyzer\NetteClassAnalyzer;
use Rector\Nette\NodeAnalyzer\RenderMethodAnalyzer;
use Rector\Nette\NodeFactory\ActionRenderFactory;
use Rector\Nette\TemplatePropertyAssignCollector;
use Rector\Nette\ValueObject\MagicTemplatePropertyCalls;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

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

    /**
     * @var RenderMethodAnalyzer
     */
    private $renderMethodAnalyzer;

    /**
     * @var NetteClassAnalyzer
     */
    private $netteClassAnalyzer;

    public function __construct(
        ActionRenderFactory $actionRenderFactory,
        TemplatePropertyAssignCollector $templatePropertyAssignCollector,
        RenderMethodAnalyzer $renderMethodAnalyzer,
        NetteClassAnalyzer $netteClassAnalyzer
    ) {
        $this->templatePropertyAssignCollector = $templatePropertyAssignCollector;
        $this->actionRenderFactory = $actionRenderFactory;
        $this->renderMethodAnalyzer = $renderMethodAnalyzer;
        $this->netteClassAnalyzer = $netteClassAnalyzer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change `$this->templates->{magic}` to `$this->template->render(..., $values)` in components',
            [
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
        if ($this->shouldSkip($node)) {
            return null;
        }

        $magicTemplatePropertyCalls = $this->templatePropertyAssignCollector->collectMagicTemplatePropertyCalls(
            $node
        );

        if ($magicTemplatePropertyCalls->hasMultipleTemplateFileExprs()) {
            return null;
        }

        $this->replaceConditionalAssignsWithVariables($node, $magicTemplatePropertyCalls);

        $renderMethodCall = $this->actionRenderFactory->createThisTemplateRenderMethodCall($magicTemplatePropertyCalls);
        $node->stmts = array_merge((array) $node->stmts, [new Expression($renderMethodCall)]);

        $this->removeNodes($magicTemplatePropertyCalls->getNodesToRemove());

        return $node;
    }

    private function shouldSkip(ClassMethod $classMethod): bool
    {
        if (! $this->isNames($classMethod, ['render', 'render*'])) {
            return true;
        }

        if (! $this->netteClassAnalyzer->isInComponent($classMethod)) {
            return true;
        }

        return $this->renderMethodAnalyzer->hasConditionalTemplateAssigns($classMethod);
    }

    private function replaceConditionalAssignsWithVariables(
        ClassMethod $classMethod,
        MagicTemplatePropertyCalls $magicTemplatePropertyCalls
    ): void {
        $this->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use (
            $magicTemplatePropertyCalls
        ): ?Assign {
            if (! $node instanceof Assign) {
                return null;
            }

            $variableName = $this->matchConditionalAssignVariableName(
                $node,
                $magicTemplatePropertyCalls->getConditionalAssigns()
            );
            if ($variableName === null) {
                return null;
            }

            return new Assign(new Variable($variableName), $node->expr);
        });
    }

    /**
     * @param array<string, Assign[]> $condtionalAssignsByName
     */
    private function matchConditionalAssignVariableName(Assign $assign, array $condtionalAssignsByName): ?string
    {
        foreach ($condtionalAssignsByName as $name => $condtionalAssigns) {
            if (! $this->betterStandardPrinter->isNodeEqual($assign, $condtionalAssigns)) {
                continue;
            }

            return $name;
        }

        return null;
    }
}
