<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Rector\Echo_;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\InlineHTML;
use Rector\CakePHPToSymfony\Rector\AbstractCakePHPRector;
use Rector\Exception\NotImplementedException;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CakePHPToSymfony\Tests\Rector\Echo_\CakePHPTemplateHToTwigRector\CakePHPTemplateHToTwigRectorTest
 */
final class CakePHPTemplateHToTwigRector extends AbstractCakePHPRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Migrate CakePHP 2.4 h() function calls to Twig', [
            new CodeSample('<h3><?php echo h($value); ?></h3>', '<h3>{{ value|escape }}</h3>'),
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
        if (! isset($node->exprs[0])) {
            return null;
        }

        $singleEchoedExpr = $node->exprs[0];
        if (! $singleEchoedExpr instanceof FuncCall) {
            return null;
        }

        if (! $this->isName($singleEchoedExpr, 'h')) {
            return null;
        }

        $funcArg = $singleEchoedExpr->args[0]->value;
        if ($funcArg instanceof Variable) {
            $templateVariable = $this->getName($funcArg);
        } else {
            throw new NotImplementedException();
        }

        $htmlContent = sprintf('{{ %s|escape }}', $templateVariable);

        return new InlineHTML($htmlContent);
    }
}
