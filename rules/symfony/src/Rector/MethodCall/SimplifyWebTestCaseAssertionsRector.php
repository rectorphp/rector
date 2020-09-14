<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://symfony.com/blog/new-in-symfony-4-3-better-test-assertions
 * @see https://github.com/symfony/symfony/pull/30813/files
 * @see \Rector\Symfony\Tests\Rector\MethodCall\SimplifyWebTestCaseAssertionsRector\SimplifyWebTestCaseAssertionsRectorTest
 */
final class SimplifyWebTestCaseAssertionsRector extends AbstractRector
{
    /**
     * @var string
     */
    private const ASSERT_SAME = 'assertSame';

    /**
     * @var MethodCall
     */
    private $getStatusCodeMethodCall;

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Simplify use of assertions in WebTestCase', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    public function test()
    {
        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testUrl()
    {
        $this->assertSame(301, $client->getResponse()->getStatusCode());
        $this->assertSame('https://example.com', $client->getResponse()->headers->get('Location'));
    }

    public function testContains()
    {
        $this->assertContains('Hello World', $crawler->filter('h1')->text());
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    public function test()
    {
         $this->assertResponseIsSuccessful();
    }

    public function testUrl()
    {
        $this->assertResponseRedirects('https://example.com', 301);
    }

    public function testContains()
    {
        $this->assertSelectorTextContains('h1', 'Hello World');
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
        $clientGetResponseMethodCall = $this->createMethodCall('client', 'getResponse');
        $this->getStatusCodeMethodCall = $this->createMethodCall($clientGetResponseMethodCall, 'getStatusCode');

        if (! $this->isInWebTestCase($node)) {
            return null;
        }

        // assertResponseIsSuccessful
        $args = [];
        $args[] = new Arg(new LNumber(200));
        $args[] = new Arg($this->getStatusCodeMethodCall);
        $methodCall = $this->createLocalMethodCall(self::ASSERT_SAME, $args);
        if ($this->areNodesEqual($node, $methodCall)) {
            return $this->createLocalMethodCall('assertResponseIsSuccessful');
        }

        // assertResponseStatusCodeSame
        $newNode = $this->processAssertResponseStatusCodeSame($node);
        if ($newNode !== null) {
            return $newNode;
        }

        // assertSelectorTextContains
        $args = $this->matchAssertContainsCrawlerArg($node);
        if ($args !== null) {
            return $this->createLocalMethodCall('assertSelectorTextContains', $args);
        }

        // 3. assertResponseRedirects
        return $this->processAssertResponseRedirects($node);
    }

    private function isInWebTestCase(Node $node): bool
    {
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if ($classLike === null) {
            return false;
        }

        return $this->isObjectType($classLike, 'Symfony\Bundle\FrameworkBundle\Test\WebTestCase');
    }

    private function processAssertResponseStatusCodeSame(Node $node): ?MethodCall
    {
        if (! $node instanceof MethodCall) {
            return null;
        }

        if (! $this->isName($node->name, self::ASSERT_SAME)) {
            return null;
        }

        if (! $this->areNodesEqual($node->args[1]->value, $this->getStatusCodeMethodCall)) {
            return null;
        }

        $statusCode = $this->getValue($node->args[0]->value);

        // handled by another methods
        if (in_array($statusCode, [200, 301], true)) {
            return null;
        }

        return $this->createLocalMethodCall('assertResponseStatusCodeSame', [$node->args[0]]);
    }

    /**
     * @return Arg[]|null
     */
    private function matchAssertContainsCrawlerArg(MethodCall $methodCall): ?array
    {
        if (! $this->isName($methodCall->name, 'assertContains')) {
            return null;
        }

        $comparedNode = $methodCall->args[1]->value;
        if (! $comparedNode instanceof MethodCall) {
            return null;
        }

        if (! $comparedNode->var instanceof MethodCall) {
            return null;
        }

        if (! $this->isVariableName($comparedNode->var->var, 'crawler')) {
            return null;
        }

        if (! $this->isName($comparedNode->name, 'text')) {
            return null;
        }

        $args = [];
        $args[] = $comparedNode->var->args[0];
        $args[] = $methodCall->args[0];

        return $args;
    }

    private function processAssertResponseRedirects(MethodCall $methodCall): ?Node
    {
        /** @var Expression|null $previousStatement */
        $previousStatement = $methodCall->getAttribute(AttributeKey::PREVIOUS_STATEMENT);
        if (! $previousStatement instanceof Expression) {
            return null;
        }

        $previousNode = $previousStatement->expr;
        if (! $previousNode instanceof MethodCall) {
            return null;
        }

        $args = [];
        $args[] = new Arg(new LNumber(301));
        $args[] = new Arg($this->getStatusCodeMethodCall);

        $match = $this->createLocalMethodCall(self::ASSERT_SAME, $args);

        if ($this->areNodesEqual($previousNode, $match)) {
            $getResponseMethodCall = $this->createMethodCall('client', 'getResponse');
            $propertyFetch = new PropertyFetch($getResponseMethodCall, 'headers');
            $clientGetLocation = $this->createMethodCall($propertyFetch, 'get', [new Arg(new String_('Location'))]);

            if (! isset($methodCall->args[1])) {
                return null;
            }

            if ($this->areNodesEqual($methodCall->args[1]->value, $clientGetLocation)) {
                $args = [];
                $args[] = $methodCall->args[0];
                $args[] = $previousNode->args[0];

                $this->removeNode($previousNode);

                return $this->createLocalMethodCall('assertResponseRedirects', $args);
            }
        }

        return null;
    }
}
