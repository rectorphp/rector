<?php

declare(strict_types=1);

namespace Rector\Transform\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Transform\ValueObject\StaticCallToNew;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://github.com/symfony/symfony/pull/35308
 *
 * @see \Rector\Transform\Tests\Rector\StaticCall\StaticCallToNewRector\StaticCallToNewRectorTest
 */
final class StaticCallToNewRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const STATIC_CALLS_TO_NEWS = 'static_calls_to_news';

    /**
     * @var StaticCallToNew[]
     */
    private $staticCallsToNews = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change static call to new instance', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $dotenv = JsonResponse::create(true);
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $dotenv = new JsonResponse();
    }
}
CODE_SAMPLE
,
                [
                    self::STATIC_CALLS_TO_NEWS => [new StaticCallToNew('JsonResponse', 'create')],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }

    /**
     * @param Node\Expr\StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->staticCallsToNews as $staticCallToNew) {
            if (! $this->isName($node->class, $staticCallToNew->getClass())) {
                continue;
            }

            if (! $this->isName($node->name, $staticCallToNew->getMethod())) {
                continue;
            }

            $class = $this->getName($node->class);
            if ($class === null) {
                continue;
            }

            return new New_(new FullyQualified($class));
        }

        return $node;
    }

    /**
     * @param array<string, StaticCallToNew[]> $configuration
     */
    public function configure(array $configuration): void
    {
        $staticCallsToNews = $configuration[self::STATIC_CALLS_TO_NEWS] ?? [];
        $this->staticCallsToNews = $staticCallsToNews;
    }
}
