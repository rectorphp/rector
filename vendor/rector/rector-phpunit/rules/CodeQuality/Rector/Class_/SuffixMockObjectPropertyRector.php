<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * @deprecated This rule is deprecated, as renaming properties can produce invalid variable names and collide with
 * existing ones. Use a coding standard to enforce the "Mock" suffix instead.
 */
final class SuffixMockObjectPropertyRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Suffix mock object property names with "Mock" to clearly separate from real objects later on', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

final class MockingEntity extends TestCase
{
    private MockObject $simpleObject;

    protected function setUp(): void
    {
        $this->simpleObject = $this->createMock(SimpleObject::class);
    }

    public function test()
    {
        $this->simpleObject->method('someMethod')->willReturn('someValue');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

final class MockingEntity extends TestCase
{
    private MockObject $simpleObjectMock;

    protected function setUp(): void
    {
        $this->simpleObjectMock = $this->createMock(SimpleObject::class);
    }

    public function test()
    {
        $this->simpleObjectMock->method('someMethod')->willReturn('someValue');
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated, as renaming properties can produce invalid variable names. Use a coding standard to enforce the "Mock" suffix instead.', self::class));
    }
}
