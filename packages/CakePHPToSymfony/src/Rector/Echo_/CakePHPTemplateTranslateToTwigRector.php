<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Rector\Echo_;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\InlineHTML;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CakePHPToSymfony\Tests\Rector\Echo_\CakePHPTemplateTranslateToTwigRector\CakePHPTemplateTranslateToTwigRectorTest
 */
final class CakePHPTemplateTranslateToTwigRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Migrate CakePHP 2.4 template method calls with translate to Twig', [
            new CodeSample('<h3><?php echo __("Actions"); ?></h3>', '<h3>{{ "Actions"|trans }}</h3>'),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Echo_::class];
    }

    /**
     * @param Echo_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $expr = $node->exprs[0];
        if (! $expr instanceof FuncCall) {
            return null;
        }

        if (! $this->isName($expr, '__')) {
            return null;
        }

        $translatedValue = $expr->args[0]->value;
        $translatedValue = $this->getValue($translatedValue);

        $html = sprintf("{{ '%s'|trans }}", $translatedValue);

        return new InlineHTML($html);
    }
}
