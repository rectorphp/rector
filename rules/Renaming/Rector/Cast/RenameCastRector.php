<?php

declare (strict_types=1);
namespace Rector\Renaming\Rector\Cast;

use PhpParser\Node;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\Cast\Double;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\Renaming\ValueObject\RenameCast;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202510\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Renaming\Rector\Cast\RenameCastRector\RenameCastRectorTest
 */
final class RenameCastRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var array<RenameCast>
     */
    private array $renameCasts = [];
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Renames casts', [new ConfiguredCodeSample('$real = (real) $real;', '$real = (float) $real;', [new RenameCast(Double::class, Double::KIND_REAL, Double::KIND_FLOAT)])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Cast::class];
    }
    /**
     * @param Cast $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->renameCasts as $renameCast) {
            $expectedClassName = $renameCast->getFromCastExprClass();
            if (!$node instanceof $expectedClassName) {
                continue;
            }
            if ($node->getAttribute(AttributeKey::KIND) !== $renameCast->getFromCastKind()) {
                continue;
            }
            $node->setAttribute(AttributeKey::KIND, $renameCast->getToCastKind());
            $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            $node->setAttribute('startTokenPos', -1);
            $node->setAttribute('endTokenPos', -1);
            return $node;
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        Assert::allIsInstanceOf($configuration, RenameCast::class);
        $this->renameCasts = $configuration;
    }
}
