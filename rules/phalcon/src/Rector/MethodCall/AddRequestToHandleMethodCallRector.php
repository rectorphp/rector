<?php

declare(strict_types=1);

namespace Rector\Phalcon\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/rectorphp/rector/issues/2408
 */

/**
 * @see \Rector\Phalcon\Tests\Rector\MethodCall\AddRequestToHandleMethodCallRector\AddRequestToHandleMethodCallRectorTest
 */
final class AddRequestToHandleMethodCallRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Add $_SERVER REQUEST_URI to method call', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($di)
    {
        $application = new \Phalcon\Mvc\Application();
        $response = $application->handle();
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($di)
    {
        $application = new \Phalcon\Mvc\Application();
        $response = $application->handle($_SERVER["REQUEST_URI"]);
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
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isObjectType($node->var, 'Phalcon\Mvc\Application')) {
            return null;
        }

        if (! $this->isName($node->name, 'handle')) {
            return null;
        }

        if ($node->args === null || $node->args !== []) {
            return null;
        }

        $node->args[] = new Arg($this->createServerRequestUri());

        return $node;
    }

    private function createServerRequestUri(): ArrayDimFetch
    {
        return new ArrayDimFetch(new Variable('_SERVER'), new String_('REQUEST_URI'));
    }
}
