<?php

declare (strict_types=1);
namespace Rector\Renaming\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\Renaming\ValueObject\RenameAnnotationByType;
use Rector\RuleDoc\CodeSample\ConfiguredCodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * @deprecated This rule is deprecated, as too niche, never used and needs project context. Write a custom rule for the exact project case instead.
 */
final class RenameAnnotationRector extends AbstractRector implements ConfigurableRectorInterface, DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Turn defined annotations above properties and methods to their new values', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    /**
     * @test
     */
    public function someMethod()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    /**
     * @scenario
     */
    public function someMethod()
    {
    }
}
CODE_SAMPLE
, [new RenameAnnotationByType('PHPUnit\Framework\TestCase', 'test', 'scenario')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class, Expression::class];
    }
    /**
     * @param Class_|Expression $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated, as too niche and part of no set; write a custom rule for the exact project case instead', self::class));
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
    }
}
