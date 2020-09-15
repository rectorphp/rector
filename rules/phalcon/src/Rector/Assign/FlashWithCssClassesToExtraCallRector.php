<?php

declare(strict_types=1);

namespace Rector\Phalcon\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/rectorphp/rector/issues/2408#issue-534441142
 *
 * @see \Rector\Phalcon\Tests\Rector\Assign\FlashWithCssClassesToExtraCallRector\FlashWithCssClassesToExtraCallRectorTest
 */
final class FlashWithCssClassesToExtraCallRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Add $cssClasses in Flash to separated method call', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $cssClasses = [];
        $flash = new Phalcon\Flash($cssClasses);
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $cssClasses = [];
        $flash = new Phalcon\Flash();
        $flash->setCssClasses($cssClasses);
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
        return [Assign::class];
    }

    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->expr instanceof New_) {
            return null;
        }

        if (! $this->isName($node->expr->class, 'Phalcon\Flash')) {
            return null;
        }

        if (! isset($node->expr->args[0])) {
            return null;
        }

        $argument = $node->expr->args[0];

        // remove arg
        unset($node->expr->args[0]);

        // change the node

        $variable = $node->var;
        $setCssClassesMethodCall = new MethodCall($variable, 'setCssClasses', [$argument]);

        $this->addNodeAfterNode($setCssClassesMethodCall, $node);

        return $node;
    }
}
