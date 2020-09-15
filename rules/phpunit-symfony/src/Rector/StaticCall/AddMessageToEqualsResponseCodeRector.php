<?php

declare(strict_types=1);

namespace Rector\PHPUnitSymfony\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\PHPUnitSymfony\Tests\Rector\StaticCall\AddMessageToEqualsResponseCodeRector\AddMessageToEqualsResponseCodeRectorTest
 */
final class AddMessageToEqualsResponseCodeRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Add response content to response code assert, so it is easier to debug', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

final class SomeClassTest extends TestCase
{
    public function test(Response $response)
    {
        $this->assertEquals(
            Response::HTTP_NO_CONTENT,
            $response->getStatusCode()
        );
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

final class SomeClassTest extends TestCase
{
    public function test(Response $response)
    {
        $this->assertEquals(
            Response::HTTP_NO_CONTENT,
            $response->getStatusCode()
            $response->getContent()
        );
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
        return [StaticCall::class, MethodCall::class];
    }

    /**
     * @param StaticCall|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node->name, 'assertEquals')) {
            return null;
        }

        // already has 3rd "message" argument
        if (isset($node->args[2])) {
            return null;
        }

        if (! $this->isHttpRequestArgument($node->args[0]->value)) {
            return null;
        }

        $parentVariable = $this->getParentOfGetStatusCode($node->args[1]->value);
        if ($parentVariable === null) {
            return null;
        }

        $getContentMethodCall = new MethodCall($parentVariable, 'getContent');

        $node->args[2] = new Arg($getContentMethodCall);

        return $node;
    }

    /**
     * $this->assertX(Response::SOME_STATUS)
     */
    private function isHttpRequestArgument(Node $node): bool
    {
        if (! $node instanceof ClassConstFetch) {
            return false;
        }

        return $this->isObjectType($node->class, 'Symfony\Component\HttpFoundation\Response');
    }

    /**
     * @return Variable|MethodCall|Expr|null
     */
    private function getParentOfGetStatusCode(Node $node): ?Node
    {
        $currentNode = $node;
        while ($currentNode instanceof MethodCall) {
            if ($this->isName($currentNode->name, 'getStatusCode')) {
                return $currentNode->var;
            }

            $currentNode = $currentNode->var;
        }

        return null;
    }
}
