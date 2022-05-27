<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Symfony\NodeAnalyzer\SymfonyTestCaseAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://symfony.com/blog/new-in-symfony-4-3-better-test-assertions
 * @changelog https://github.com/symfony/symfony/pull/30813
 *
 * @see \Rector\Symfony\Tests\Rector\MethodCall\WebTestCaseAssertIsSuccessfulRector\WebTestCaseAssertIsSuccessfulRectorTest
 */
final class WebTestCaseAssertIsSuccessfulRector extends \Rector\Core\Rector\AbstractRector
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
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Simplify use of assertions in WebTestCase', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    public function test()
    {
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
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
        if (!$this->testsNodeAnalyzer->isAssertMethodCallName($node, 'assertSame')) {
            return null;
        }
        $args = $node->getArgs();
        if (!$this->valueResolver->isValue($args[0]->value, 200)) {
            return null;
        }
        $secondArg = $args[1]->value;
        if (!$secondArg instanceof \PhpParser\Node\Expr\MethodCall) {
            return null;
        }
        if (!$this->isName($secondArg->name, 'getStatusCode')) {
            return null;
        }
        $node->name = new \PhpParser\Node\Identifier('assertResponseIsSuccessful');
        $node->args = [];
        return $node;
    }
}
