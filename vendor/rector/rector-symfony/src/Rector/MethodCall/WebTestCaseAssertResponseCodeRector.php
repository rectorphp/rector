<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Symfony\NodeAnalyzer\SymfonyTestCaseAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://symfony.com/blog/new-in-symfony-4-3-better-test-assertions
 * @changelog https://github.com/symfony/symfony/pull/30813
 *
 * @see \Rector\Symfony\Tests\Rector\MethodCall\WebTestCaseAssertResponseCodeRector\WebTestCaseAssertResponseCodeRectorTest
 */
final class WebTestCaseAssertResponseCodeRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\SymfonyTestCaseAnalyzer
     */
    private $symfonyTestCaseAnalyzer;
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(SymfonyTestCaseAnalyzer $symfonyTestCaseAnalyzer, TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->symfonyTestCaseAnalyzer = $symfonyTestCaseAnalyzer;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Simplify use of assertions in WebTestCase', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SomeClass extends WebTestCase
{
    public function test()
    {
        $response = self::getClient()->getResponse();

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame('https://example.com', $response->headers->get('Location'));
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SomeClass extends WebTestCase
{
    public function test()
    {
        $this->assertResponseStatusCodeSame(301);
        $this->assertResponseRedirects('https://example.com');
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
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->symfonyTestCaseAnalyzer->isInWebTestCase($node)) {
            return null;
        }
        // assertResponseStatusCodeSame
        $newMethodCall = $this->processAssertResponseStatusCodeSame($node);
        if ($newMethodCall !== null) {
            return $newMethodCall;
        }
        return $this->processAssertResponseRedirects($node);
    }
    /**
     * We look for: "$client->getResponse()->headers->get('Location')"
     */
    public function isGetLocationMethodCall(Expr $expr) : bool
    {
        if (!$expr instanceof MethodCall) {
            return \false;
        }
        if (!$this->isName($expr->name, 'get')) {
            return \false;
        }
        $args = $expr->getArgs();
        if ($args === []) {
            return \false;
        }
        $firstArg = $args[0];
        return $this->valueResolver->isValue($firstArg->value, 'Location');
    }
    private function processAssertResponseStatusCodeSame(MethodCall $methodCall) : ?MethodCall
    {
        if (!$this->isName($methodCall->name, 'assertSame')) {
            return null;
        }
        $args = $methodCall->getArgs();
        $secondArg = $args[1];
        if (!$secondArg->value instanceof MethodCall) {
            return null;
        }
        $nestedMethodCall = $secondArg->value;
        if (!$this->nodeNameResolver->isName($nestedMethodCall->name, 'getStatusCode')) {
            return null;
        }
        $statusCode = $this->valueResolver->getValue($args[0]->value);
        if ($statusCode === null) {
            return null;
        }
        // handled by another methods
        if ($statusCode === 200) {
            return null;
        }
        return $this->nodeFactory->createLocalMethodCall('assertResponseStatusCodeSame', [$methodCall->args[0]]);
    }
    private function processAssertResponseRedirects(MethodCall $methodCall) : ?MethodCall
    {
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($methodCall, ['assertSame'])) {
            return null;
        }
        $args = $methodCall->getArgs();
        $firstArgValue = $args[1]->value;
        if (!$this->isGetLocationMethodCall($firstArgValue)) {
            return null;
        }
        $expectedUrl = $args[0]->value;
        return $this->nodeFactory->createLocalMethodCall('assertResponseRedirects', [$expectedUrl]);
    }
}
