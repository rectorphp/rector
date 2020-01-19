<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Rector\Echo_;

use PhpParser\Node;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**

 * @see \Rector\CakePHPToSymfony\Tests\Rector\Echo_\CakePHPTemplateHToTwigRector\CakePHPTemplateHToTwigRectorTest
 */
final class CakePHPTemplateHToTwigRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Migrate CakePHP 2.4 h() function calls to Twig', [
            new CodeSample(
                '3><?php echo h($value); ?></h3>',
                '3>{{ value|escape }}</h3>'
            )
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [\PhpParser\Node\Stmt\Echo_::class];
    }

    /**
     * @param \PhpParser\Node\Stmt\Echo_ $node
     */
    public function refactor(Node $node): ?Node
    {
        // change the node

        return $node;
    }
}
