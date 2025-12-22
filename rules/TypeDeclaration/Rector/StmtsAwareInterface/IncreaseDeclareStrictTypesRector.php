<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\StmtsAwareInterface;

use PhpParser\Node;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\PhpParser\Enum\NodeGroup;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated As keeps changing files randomly on every run. Not deterministic. Use more reliable @see \Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector instead on specific paths.
 */
final class IncreaseDeclareStrictTypesRector extends AbstractRector implements ConfigurableRectorInterface, DeprecatedInterface
{
    public const LIMIT = 'limit';
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add declare strict types to a limited amount of classes at a time, to try out in the wild and increase level gradually', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
function someFunction()
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
declare(strict_types=1);

function someFunction()
{
}
CODE_SAMPLE
, [self::LIMIT => 10])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return NodeGroup::STMTS_AWARE;
    }
    /**
     * @param StmtsAware $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated as changes strict types randomly on each run. Use "%s" Rector on specific paths instead.', self::class, \Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector::class));
    }
    /**
     * @param array<string, mixed> $configuration
     */
    public function configure(array $configuration): void
    {
    }
}
