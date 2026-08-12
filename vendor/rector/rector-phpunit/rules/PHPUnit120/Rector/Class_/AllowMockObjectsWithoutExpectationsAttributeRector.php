<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit120\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated This rule is deprecated, as the attribute only silences the notice. The correct fix is to add the missing
 * expects() to the test methods that use the mock, which requires knowing the test intent and cannot be automated.
 */
final class AllowMockObjectsWithoutExpectationsAttributeRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add #[AllowMockObjectsWithoutExpectations] attribute to PHPUnit test classes with mock properties used in multiple methods but one, to avoid irrelevant notices in tests run', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
final class SomeTest extends TestCase
{
    private \PHPUnit\Framework\MockObject\MockObject $someServiceMock;

    protected function setUp(): void
    {
        $this->someServiceMock = $this->createMock(SomeService::class);
    }

    public function testOne(): void
    {
        // use $this->someServiceMock
    }

    public function testTwo(): void
    {
        // use $this->someServiceMock
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

#[AllowMockObjectsWithoutExpectations]
final class SomeTest extends TestCase
{
    private \PHPUnit\Framework\MockObject\MockObject $someServiceMock;

    protected function setUp(): void
    {
        $this->someServiceMock = $this->createMock(SomeService::class);
    }

    public function testOne(): void
    {
        $this->someServiceMock->expects($this->once())
            ->method('someMethod')
            ->willReturn('someValue');
    }

    public function testTwo(): void
    {
        $this->someServiceMock->expects($this->once())
            ->method('someMethod')
            ->willReturn('anotherValue');
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
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated, as the attribute only silences the notice. Add the missing expects() to the test methods that use the mock instead.', self::class));
    }
}
