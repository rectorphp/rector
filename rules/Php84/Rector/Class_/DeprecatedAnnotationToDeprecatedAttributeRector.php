<?php

declare (strict_types=1);
namespace Rector\Php84\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\PhpAttribute\DeprecatedAnnotationToDeprecatedAttributeConverter;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php84\Rector\Class_\DeprecatedAnnotationToDeprecatedAttributeRector\DeprecatedAnnotationToDeprecatedAttributeRectorTest
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
 * @deprecated 1.0.0 Use SomeOtherFunction instead
 */
function someFunction()
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
#[\Deprecated(message: 'Use SomeOtherFunction instead', since: '1.0.0')]
function someFunction()
{
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [Function_::class, ClassMethod::class, ClassConst::class];
    }
    /**
     * @param ClassConst|Function_|ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        return $this->deprecatedAnnotationToDeprecatedAttributeConverter->convert($node);
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::DEPRECATED_ATTRIBUTE;
    }
}
