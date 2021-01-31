<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\Nette\NodeAnalyzer\RenderMethodAnalyzer;
use Rector\Nette\NodeFactory\ActionRenderFactory;
use Rector\Nette\TemplatePropertyAssignCollector;
use Rector\NodeTypeResolver\Node\AttributeKey;
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

    public function __construct(
        ActionRenderFactory $actionRenderFactory,
        TemplatePropertyAssignCollector $templatePropertyAssignCollector,
        RenderMethodAnalyzer $renderMethodAnalyzer
    ) {
        $this->templatePropertyAssignCollector = $templatePropertyAssignCollector;
        $this->actionRenderFactory = $actionRenderFactory;
        $this->renderMethodAnalyzer = $renderMethodAnalyzer;
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

        $renderMethodCall = $this->actionRenderFactory->createThisTemplateRenderMethodCall($magicTemplatePropertyCalls);
        $node->stmts = array_merge((array) $node->stmts, [new Expression($renderMethodCall)]);

        $this->removeNodes($magicTemplatePropertyCalls->getNodesToRemove());

        return $node;
    }

    private function shouldSkip(ClassMethod $classMethod): bool
    {
        $classLike = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return true;
        }

        if (! $this->isObjectType($classLike, 'Nette\Application\UI\Control')) {
            return true;
        }

        if ($this->isObjectType($classLike, 'Nette\Application\UI\Presenter')) {
            return true;
        }

        if (! $this->isNames($classMethod, ['render', 'render*'])) {
            return true;
        }

        $hasReturn = (bool) $this->betterNodeFinder->findInstanceOf($classLike, Return_::class);
        if ($hasReturn) {
            return true;
        }

        return $this->renderMethodAnalyzer->hasConditionalTemplateAssigns($classMethod);
    }
}
