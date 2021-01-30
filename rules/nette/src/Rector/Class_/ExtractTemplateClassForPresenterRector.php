<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\Class_;

use PhpParser\Node;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://blog.nette.org/en/latte-how-to-use-type-system#toc-how-to-declare-types
 *
 * @see \Rector\Nette\Tests\Rector\Class_\ExtractTemplateClassForPresenterRector\ExtractTemplateClassForPresenterRectorTest
 */
final class ExtractTemplateClassForPresenterRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Decouples template class for presenter', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Nette\Application\UI\Presenter;

final class SomePresenter extends Presenter
{
    public function render()
    {
    }
}

// some_template.latte
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
use Nette\Application\UI\Presenter;

/**
 * @property SomeTemplate $template
 */
final class SomePresenter extends Presenter
{
    public function render()
    {
    }
}
CODE_SAMPLE

            )
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [\PhpParser\Node\Stmt\Class_::class];
    }

    /**
     * @param \PhpParser\Node\Stmt\Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        // change the node

        return $node;
    }
}
