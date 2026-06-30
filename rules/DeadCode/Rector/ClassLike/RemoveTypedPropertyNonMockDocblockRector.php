<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassLike;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated This rule is deprecated as it has no real value, removing a single narrow @var docblock combination on a typed property.
 */
final class RemoveTypedPropertyNonMockDocblockRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove @var annotation for PHPUnit\Framework\MockObject\MockObject combined with native object type', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

final class SomeTest extends TestCase
{
    /**
     * @var SomeClass|MockObject
     */
    private SomeClass $someProperty;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

final class SomeTest extends TestCase
{
    private SomeClass $someProperty;
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
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated as it has no real value', self::class));
    }
}
