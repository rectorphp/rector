<?php

declare (strict_types=1);
namespace Rector\Symfony\Configs\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated Far-fetched and not reliable, as it pairs constructor params with config arguments by name and position,
 * across configs the rule has no way to tell apart. Custom autowiring should be handled with care, one service at a time.
 */
final class AutowireAttributeRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change explicit configuration parameter pass into #[Autowire] attributes', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function __construct(
        private int $timeout,
        private string $secret,
    )  {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class SomeClass
{
    public function __construct(
        #[Autowire(param: 'timeout')]
        private int $timeout,
        #[Autowire(env: 'APP_SECRET')]
        private string $secret,
    )  {
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Class_
    {
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated, as pairing constructor params with config arguments by name and position is far-fetched and not reliable. Handle custom autowiring with care, one service at a time', self::class));
    }
}
