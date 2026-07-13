<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony43\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated This rule hides the asserted behavior and forces different assert methods for different status codes. Assert the HTTP status code directly instead.
 */
final class WebTestCaseAssertIsSuccessfulRector extends AbstractRector implements DeprecatedInterface
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated, as it hides the asserted behavior and forces different assert methods for different status codes. Assert the HTTP status code directly instead', self::class));
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Simplify use of assertions in WebTestCase', [new CodeSample(<<<'CODE_SAMPLE'
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
}
