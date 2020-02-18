<?php

declare(strict_types=1);

namespace Rector\Zend2Rocket\Rector\ClassMethod;

use PhpParser\Node;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**

 * @see \Rector\Zend2Rocket\Tests\Rector\ClassMethod\RenderZendViewRector\RenderZendViewRectorTest
 */
final class RenderZendViewRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('call 'currentZendViewResult' in all Action-methods that do not call 'setNoRender'', [
            new CodeSample(
                <<<'PHP'
class SomeController {
    public function getArticleInfoAction()
    {
    }
}
PHP
,
                <<<'PHP'
class SomeController {
    public function getArticleInfoAction()
    {
        return $this->currentZendViewResult();
    }
}
PHP

            )
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }

    /**
     * @param \PhpParser\Node\Stmt\ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        // change the node

        return $node;
    }
}
