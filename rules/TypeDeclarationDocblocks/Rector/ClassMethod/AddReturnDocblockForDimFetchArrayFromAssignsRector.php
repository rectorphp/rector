<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated This rule is deprecated, as the array shape is guessed from conditional assigns. The result is vague and unreliable, as any later assign can widen the type. Add the @return docblock manually instead.
 */
final class AddReturnDocblockForDimFetchArrayFromAssignsRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add @return docblock for methods returning array from dim fetch of assigned arrays', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function toArray(): array
    {
        $items = [];

        if (mt_rand(0, 1)) {
            $items['key'] = 'value';
        }

        if (mt_rand(0, 1)) {
            $items['another_key'] = 'another_value';
        }

        return $items;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @return array<string, string>
     */
    public function toArray()
    {
        $items = [];

        if (mt_rand(0, 1)) {
            $items['key'] = 'value';
        }

        if (mt_rand(0, 1)) {
            $items['another_key'] = 'another_value';
        }

        return $items;
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?ClassMethod
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated, as the array shape guessed from conditional assigns is vague and unreliable. Add the @return docblock manually instead', self::class));
    }
}
