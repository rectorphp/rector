<?php

declare(strict_types=1);

namespace Rector\Zend2Rocket\Rector\ClassMethod;

use PhpParser\Node;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

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
        // identify if classmethod is an Actionmethod
        if (! $this->controllerMethodAnalyzer->isAction($node)) {
            return $node;
        }

        // check if classmethod calls setNoRender
        $subnodes = $node->getSubNodeNames();
        if ( in_array('setNoRender', $subnodes)) {
            return $node;
        }

        // serve Zendview
        return $this->refactorZendViewRender($node);
    }

    private function refactorZendViewRender(ClassMethod $node)
    {
        // build AST of 'return $this->currentZendViewResult();'
        $methodcall = $this->createMethodCall('this', 'currentZendViewResult', []);
        $return = new Return_($methodcall);
        $node->setAttribute(AttributeKey::PARENT_NODE, $return);

        return $return;
    }
}
