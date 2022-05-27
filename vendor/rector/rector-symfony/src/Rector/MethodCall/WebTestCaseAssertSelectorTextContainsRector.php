<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Symfony\NodeAnalyzer\SymfonyTestCaseAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://symfony.com/blog/new-in-symfony-4-3-better-test-assertions
 * @changelog https://github.com/symfony/symfony/pull/30813
 *
 * @see \Rector\Symfony\Tests\Rector\MethodCall\WebTestCaseAssertSelectorTextContainsRector\WebTestCaseAssertSelectorTextContainsRectorTest
 */
final class WebTestCaseAssertSelectorTextContainsRector extends \Rector\Core\Rector\AbstractRector
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
    public function __construct(\Rector\Symfony\NodeAnalyzer\SymfonyTestCaseAnalyzer $symfonyTestCaseAnalyzer, \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->symfonyTestCaseAnalyzer = $symfonyTestCaseAnalyzer;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Simplify use of assertions in WebTestCase to assertSelectorTextContains()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

final class SomeTest extends WebTestCase
{
    public function testContains()
    {
        $crawler = new Symfony\Component\DomCrawler\Crawler();
        $this->assertContains('Hello World', $crawler->filter('h1')->text());
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

final class SomeTest extends WebTestCase
{
    public function testContains()
    {
        $crawler = new Symfony\Component\DomCrawler\Crawler();
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
        return [\PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->symfonyTestCaseAnalyzer->isInWebTestCase($node)) {
            return null;
        }
        if (!$this->testsNodeAnalyzer->isAssertMethodCallName($node, 'assertContains')) {
            return null;
        }
        $args = $node->getArgs();
        $firstArgValue = $args[1]->value;
        if (!$firstArgValue instanceof \PhpParser\Node\Expr\MethodCall) {
            return null;
        }
        $methodCall = $firstArgValue;
        if (!$this->isName($methodCall->name, 'text')) {
            return null;
        }
        if (!$methodCall->var instanceof \PhpParser\Node\Expr\MethodCall) {
            return null;
        }
        $nestedMethodCall = $methodCall->var;
        if (!$this->isName($nestedMethodCall->name, 'filter')) {
            return null;
        }
        $newArgs = [$nestedMethodCall->args[0], $args[0]];
        return $this->nodeFactory->createLocalMethodCall('assertSelectorTextContains', $newArgs);
    }
}
