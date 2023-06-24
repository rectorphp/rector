<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony43\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\NodeAnalyzer\ExprAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Symfony\NodeAnalyzer\SymfonyTestCaseAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://symfony.com/blog/new-in-symfony-4-3-better-test-assertions
 * @changelog https://github.com/symfony/symfony/pull/30813
 *
 * @see \Rector\Symfony\Tests\Symfony43\Rector\MethodCall\WebTestCaseAssertSelectorTextContainsRector\WebTestCaseAssertSelectorTextContainsRectorTest
 */
final class WebTestCaseAssertSelectorTextContainsRector extends AbstractRector
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
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ExprAnalyzer
     */
    private $exprAnalyzer;
    public function __construct(SymfonyTestCaseAnalyzer $symfonyTestCaseAnalyzer, TestsNodeAnalyzer $testsNodeAnalyzer, ExprAnalyzer $exprAnalyzer)
    {
        $this->symfonyTestCaseAnalyzer = $symfonyTestCaseAnalyzer;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->exprAnalyzer = $exprAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Simplify use of assertions in WebTestCase to assertSelectorTextContains()', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->symfonyTestCaseAnalyzer->isInWebTestCase($node)) {
            return null;
        }
        if (!$this->testsNodeAnalyzer->isAssertMethodCallName($node, 'assertContains')) {
            return null;
        }
        $args = $node->getArgs();
        $firstArgValue = $args[1]->value;
        if (!$firstArgValue instanceof MethodCall) {
            return null;
        }
        $methodCall = $firstArgValue;
        if (!$this->isName($methodCall->name, 'text')) {
            return null;
        }
        if (!$methodCall->var instanceof MethodCall) {
            return null;
        }
        $nestedMethodCall = $methodCall->var;
        if (!$this->isName($nestedMethodCall->name, 'filter')) {
            return null;
        }
        $newArgs = [$nestedMethodCall->args[0], $args[0]];
        // When we had a custom message argument we want to add it to the new assert.
        if (isset($args[2])) {
            if ($this->exprAnalyzer->isDynamicExpr($args[2]->value)) {
                $newArgs[] = $args[2]->value;
            } else {
                $newArgs[] = new Arg(new String_($this->valueResolver->getValue($args[2]->value, \true)));
            }
        }
        return $this->replaceFunctionCall($node, $newArgs);
    }
    /**
     * @param Node[] $newArgs
     */
    private function replaceFunctionCall(Node $node, array $newArgs) : Node
    {
        if ($node instanceof StaticCall) {
            return $this->nodeFactory->createStaticCall('self', 'assertSelectorTextContains', $newArgs);
        }
        return $this->nodeFactory->createLocalMethodCall('assertSelectorTextContains', $newArgs);
    }
}
