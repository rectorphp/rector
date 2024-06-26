<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony43\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated This rule is deprecated since Rector 1.1.2, as it does not upgrade to valid code.
 */
final class WebTestCaseAssertSelectorTextContainsRector extends AbstractRector implements DeprecatedInterface
{
    /**
     * @var bool
     */
    private $hasWarned = \false;
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
        if ($this->hasWarned) {
            return null;
        }
        \trigger_error(\sprintf('The "%s" rule was deprecated, as it does not upgrade to valid code.', self::class));
        \sleep(3);
        $this->hasWarned = \true;
        return null;
    }
}
