<?php

declare (strict_types=1);
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
use RectorPrefix20211020\Webmozart\Assert\Assert;
/**
 * @changelog https://github.com/symfony/symfony/pull/35308
 *
 * @see \Rector\Tests\Transform\Rector\StaticCall\StaticCallToNewRector\StaticCallToNewRectorTest
 */
final class StaticCallToNewRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const STATIC_CALLS_TO_NEWS = 'static_calls_to_news';
    /**
     * @var StaticCallToNew[]
     */
    private $staticCallsToNews = [];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change static call to new instance', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $dotenv = JsonResponse::create(true);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $dotenv = new JsonResponse();
    }
}
CODE_SAMPLE
, [self::STATIC_CALLS_TO_NEWS => [new \Rector\Transform\ValueObject\StaticCallToNew('JsonResponse', 'create')]])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param Node\Expr\StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        foreach ($this->staticCallsToNews as $staticCallToNews) {
            if (!$this->isName($node->class, $staticCallToNews->getClass())) {
                continue;
            }
            if (!$this->isName($node->name, $staticCallToNews->getMethod())) {
                continue;
            }
            $class = $this->getName($node->class);
            if ($class === null) {
                continue;
            }
            return new \PhpParser\Node\Expr\New_(new \PhpParser\Node\Name\FullyQualified($class));
        }
        return $node;
    }
    /**
     * @param array<string, StaticCallToNew[]> $configuration
     */
    public function configure(array $configuration) : void
    {
        $staticCallsToNews = $configuration[self::STATIC_CALLS_TO_NEWS] ?? [];
        \RectorPrefix20211020\Webmozart\Assert\Assert::allIsAOf($staticCallsToNews, \Rector\Transform\ValueObject\StaticCallToNew::class);
        $this->staticCallsToNews = $staticCallsToNews;
    }
}
