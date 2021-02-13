<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Nette\NodeAnalyzer\ConditionalTemplateAssignReplacer;
use Rector\Nette\NodeAnalyzer\NetteClassAnalyzer;
use Rector\Nette\NodeAnalyzer\RenderMethodAnalyzer;
use Rector\Nette\NodeAnalyzer\TemplatePropertyAssignCollector;
use Rector\Nette\NodeFactory\RenderParameterArrayFactory;
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
     * @var RenderMethodAnalyzer
     */
    private $renderMethodAnalyzer;

    /**
     * @var NetteClassAnalyzer
     */
    private $netteClassAnalyzer;

    /**
     * @var RenderParameterArrayFactory
     */
    private $renderParameterArrayFactory;

    /**
     * @var ConditionalTemplateAssignReplacer
     */
    private $conditionalTemplateAssignReplacer;

    public function __construct(
        TemplatePropertyAssignCollector $templatePropertyAssignCollector,
        RenderMethodAnalyzer $renderMethodAnalyzer,
        NetteClassAnalyzer $netteClassAnalyzer,
        RenderParameterArrayFactory $renderParameterArrayFactory,
        ConditionalTemplateAssignReplacer $conditionalTemplateAssignReplacer
    ) {
        $this->templatePropertyAssignCollector = $templatePropertyAssignCollector;
        $this->renderMethodAnalyzer = $renderMethodAnalyzer;
        $this->netteClassAnalyzer = $netteClassAnalyzer;
        $this->renderParameterArrayFactory = $renderParameterArrayFactory;
        $this->conditionalTemplateAssignReplacer = $conditionalTemplateAssignReplacer;
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

        $renderMethodCall = $this->renderMethodAnalyzer->machRenderMethodCall($node);
        if (! $renderMethodCall instanceof MethodCall) {
            return null;
        }

        if (! isset($renderMethodCall->args[0])) {
            return null;
        }

        $magicTemplatePropertyCalls = $this->templatePropertyAssignCollector->collectMagicTemplatePropertyCalls(
            $node
        );

        $array = $this->renderParameterArrayFactory->createArray($magicTemplatePropertyCalls);
        if (! $array instanceof Array_) {
            return null;
        }

        $this->conditionalTemplateAssignReplacer->processClassMethod($node, $magicTemplatePropertyCalls);
        $renderMethodCall->args[1] = new Arg($array);

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
}
