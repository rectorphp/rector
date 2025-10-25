<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Foreach_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated as not part of any set and not widely used. Can create less readable code, harder to analyse, read and work with.
 */
final class MultiDimensionalArrayToArrayDestructRector extends AbstractRector implements MinPhpVersionInterface, DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change multidimensional array access in foreach to array destruct', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @param array<int, array{id: int, name: string}> $users
     */
    public function run(array $users)
    {
        foreach ($users as $user) {
            echo $user['id'];
            echo sprintf('Name: %s', $user['name']);
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @param array<int, array{id: int, name: string}> $users
     */
    public function run(array $users)
    {
        foreach ($users as ['id' => $id, 'name' => $name]) {
            echo $id;
            echo sprintf('Name: %s', $name);
        }
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Foreach_::class];
    }
    /**
     * @param Foreach_ $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated as not part of any set and not widely used. Can create less readable code, harder to analyse, read and work with', self::class));
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::ARRAY_DESTRUCT;
    }
}
