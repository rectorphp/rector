<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://symfony.com/blog/new-in-symfony-4-3-better-test-assertions
 * @see https://github.com/symfony/symfony/pull/30813/files
 *
 * @see \Rector\Symfony\Tests\Rector\MethodCall\SimplifyWebTestCaseAssertionsRector\SimplifyWebTestCaseAssertionsRectorTest
 */
final class SimplifyWebTestCaseAssertionsRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const ASSERT_SAME = 'assertSame';
    /**
     * @var \PhpParser\Node\Expr\MethodCall|null
     */
    private $getStatusCodeMethodCall;
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Simplify use of assertions in WebTestCase', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
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
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isInWebTestCase($node)) {
            return null;
        }
        $clientGetResponseMethodCall = $this->nodeFactory->createMethodCall('client', 'getResponse');
        $this->getStatusCodeMethodCall = $this->nodeFactory->createMethodCall($clientGetResponseMethodCall, 'getStatusCode');
        // assertResponseIsSuccessful
        $args = [];
        $args[] = new \PhpParser\Node\Arg(new \PhpParser\Node\Scalar\LNumber(200));
        $args[] = new \PhpParser\Node\Arg($this->getGetStatusCodeMethodCall());
        $methodCall = $this->nodeFactory->createLocalMethodCall(self::ASSERT_SAME, $args);
        if ($this->nodeComparator->areNodesEqual($node, $methodCall)) {
            return $this->nodeFactory->createLocalMethodCall('assertResponseIsSuccessful');
        }
        // assertResponseStatusCodeSame
        $newNode = $this->processAssertResponseStatusCodeSame($node);
        if ($newNode !== null) {
            return $newNode;
        }
        // assertSelectorTextContains
        $args = $this->matchAssertContainsCrawlerArg($node);
        if ($args !== null) {
            return $this->nodeFactory->createLocalMethodCall('assertSelectorTextContains', $args);
        }
        return $this->processAssertResponseRedirects($node);
    }
    private function isInWebTestCase(\PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        $scope = $methodCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return \false;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return \false;
        }
        return $classReflection->isSubclassOf('Symfony\\Bundle\\FrameworkBundle\\Test\\WebTestCase');
    }
    private function processAssertResponseStatusCodeSame(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node\Expr\MethodCall
    {
        if (!$this->isName($methodCall->name, self::ASSERT_SAME)) {
            return null;
        }
        $secondArg = $methodCall->args[1];
        if (!$secondArg instanceof \PhpParser\Node\Arg) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($secondArg->value, $this->getGetStatusCodeMethodCall())) {
            return null;
        }
        $firstArg = $methodCall->args[0];
        if (!$firstArg instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $statusCode = $this->valueResolver->getValue($firstArg->value);
        // handled by another methods
        if (\in_array($statusCode, [200, 301], \true)) {
            return null;
        }
        return $this->nodeFactory->createLocalMethodCall('assertResponseStatusCodeSame', [$methodCall->args[0]]);
    }
    /**
     * @return Arg[]|null
     */
    private function matchAssertContainsCrawlerArg(\PhpParser\Node\Expr\MethodCall $methodCall) : ?array
    {
        if (!$this->isName($methodCall->name, 'assertContains')) {
            return null;
        }
        $secondArg = $methodCall->args[1];
        if (!$secondArg instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $comparedNode = $secondArg->value;
        if (!$comparedNode instanceof \PhpParser\Node\Expr\MethodCall) {
            return null;
        }
        if (!$comparedNode->var instanceof \PhpParser\Node\Expr\MethodCall) {
            return null;
        }
        $comparedMethodCaller = $comparedNode->var;
        if (!$comparedMethodCaller->var instanceof \PhpParser\Node\Expr\Variable) {
            return null;
        }
        if (!$this->isName($comparedMethodCaller->var, 'crawler')) {
            return null;
        }
        if (!$this->isName($comparedNode->name, 'text')) {
            return null;
        }
        $args = [];
        $args[] = $comparedNode->var->args[0];
        $args[] = $methodCall->args[0];
        /** @var Arg[] $args */
        return $args;
    }
    private function processAssertResponseRedirects(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node
    {
        /** @var Expression|null $previousStatement */
        $previousStatement = $methodCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_STATEMENT);
        if (!$previousStatement instanceof \PhpParser\Node\Stmt\Expression) {
            return null;
        }
        $previousNode = $previousStatement->expr;
        if (!$previousNode instanceof \PhpParser\Node\Expr\MethodCall) {
            return null;
        }
        $args = [];
        $args[] = new \PhpParser\Node\Arg(new \PhpParser\Node\Scalar\LNumber(301));
        $args[] = new \PhpParser\Node\Arg($this->getGetStatusCodeMethodCall());
        $match = $this->nodeFactory->createLocalMethodCall(self::ASSERT_SAME, $args);
        if ($this->nodeComparator->areNodesEqual($previousNode, $match)) {
            $getResponseMethodCall = $this->nodeFactory->createMethodCall('client', 'getResponse');
            $propertyFetch = new \PhpParser\Node\Expr\PropertyFetch($getResponseMethodCall, 'headers');
            $clientGetLocation = $this->nodeFactory->createMethodCall($propertyFetch, 'get', [new \PhpParser\Node\Arg(new \PhpParser\Node\Scalar\String_('Location'))]);
            if (!isset($methodCall->args[1])) {
                return null;
            }
            $firstArg = $methodCall->args[1];
            if (!$firstArg instanceof \PhpParser\Node\Arg) {
                return null;
            }
            if ($this->nodeComparator->areNodesEqual($firstArg->value, $clientGetLocation)) {
                $args = [];
                $args[] = $methodCall->args[0];
                $args[] = $previousNode->args[0];
                $this->removeNode($previousNode);
                return $this->nodeFactory->createLocalMethodCall('assertResponseRedirects', $args);
            }
        }
        return null;
    }
    private function getGetStatusCodeMethodCall() : \PhpParser\Node\Expr\MethodCall
    {
        if ($this->getStatusCodeMethodCall === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        return $this->getStatusCodeMethodCall;
    }
}
