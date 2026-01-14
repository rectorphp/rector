<?php

declare (strict_types=1);
namespace Rector\Php85\Rector\Const_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Const_;
use PhpParser\Node\Stmt\Trait_;
use Rector\PhpAttribute\DeprecatedAnnotationToDeprecatedAttributeConverter;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersion;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://wiki.php.net/rfc/attributes-on-constants
 * @see https://wiki.php.net/rfc/deprecated_traits
 *
 * @see \Rector\Tests\Php85\Rector\Const_\ConstAndTraitDeprecatedAttributeRector\ConstAndTraitDeprecatedAttributeRectorTest
 */
final class ConstAndTraitDeprecatedAttributeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private DeprecatedAnnotationToDeprecatedAttributeConverter $deprecatedAnnotationToDeprecatedAttributeConverter;
    public function __construct(DeprecatedAnnotationToDeprecatedAttributeConverter $deprecatedAnnotationToDeprecatedAttributeConverter)
    {
        $this->deprecatedAnnotationToDeprecatedAttributeConverter = $deprecatedAnnotationToDeprecatedAttributeConverter;
    }
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
        return $this->deprecatedAnnotationToDeprecatedAttributeConverter->convert($node);
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersion::PHP_85;
    }
}
