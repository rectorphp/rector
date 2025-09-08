<?php

declare (strict_types=1);
namespace Rector\Php85\Rector\Const_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Const_;
use Rector\PhpAttribute\DeprecatedAnnotationToDeprecatedAttributeConverter;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php85\Rector\Const_\DeprecatedAnnotationToDeprecatedAttributeRector\DeprecatedAnnotationToDeprecatedAttributeRectorTest
 */
final class DeprecatedAnnotationToDeprecatedAttributeRector extends AbstractRector implements MinPhpVersionInterface
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
        return new RuleDefinition('Change @deprecated annotation to Deprecated attribute', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Const_::class];
    }
    /**
     * @param Const_ $node
     */
    public function refactor(Node $node): ?Node
    {
        return $this->deprecatedAnnotationToDeprecatedAttributeConverter->convert($node);
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::DEPRECATED_ATTRIBUTE_ON_CONSTANT;
    }
}
