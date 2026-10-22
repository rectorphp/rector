<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\String_;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\ConfiguredCodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\Transform\ValueObject\StringToClassConstant;
/**
 * @deprecated This rule is deprecated, as too niche, never used and needs project context. Write a custom rule for the exact project case instead.
 */
final class StringToClassConstantRector extends AbstractRector implements ConfigurableRectorInterface, DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change strings to specific constants', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
final class SomeSubscriber
{
    public static function getSubscribedEvents()
    {
        return ['compiler.post_dump' => 'compile'];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeSubscriber
{
    public static function getSubscribedEvents()
    {
        return [\Yet\AnotherClass::CONSTANT => 'compile'];
    }
}
CODE_SAMPLE
, [new StringToClassConstant('compiler.post_dump', 'Yet\AnotherClass', 'CONSTANT')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [String_::class];
    }
    /**
     * @param String_ $node
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
