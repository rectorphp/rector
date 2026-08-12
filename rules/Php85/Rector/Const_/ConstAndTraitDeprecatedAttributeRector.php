<?php

declare (strict_types=1);
namespace Rector\Php85\Rector\Const_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Const_;
use PhpParser\Node\Stmt\Trait_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersion;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated This rule is deprecated, as the #[Deprecated] attribute triggers a runtime deprecation, while the @deprecated annotation is a static hint only. Those have a different purpose and are not interchangeable. Use "phpstan/phpstan-deprecation-rules" to report the annotation instead.
 */
final class ConstAndTraitDeprecatedAttributeRector extends AbstractRector implements MinPhpVersionInterface, DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change @deprecated annotation to #[Deprecated] attribute for constants', [new CodeSample(<<<'CODE_SAMPLE'
/**
 * @deprecated 1.0.0 Use SomeOtherConstant instead
 */
const SomeConstant = 'irrelevant';
CODE_SAMPLE
, <<<'CODE_SAMPLE'
#[\Deprecated(message: 'Use SomeOtherConstant instead', since: '1.0.0')]
const SomeConstant = 'irrelevant';
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [Const_::class, Trait_::class];
    }
    /**
     * @param Const_|Trait_ $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated, as the #[Deprecated] attribute triggers a runtime deprecation, unlike the @deprecated annotation; use "phpstan/phpstan-deprecation-rules" to report the annotation instead', self::class));
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersion::PHP_85;
    }
}
