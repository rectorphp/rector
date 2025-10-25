<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Stmt\Expression;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated as might create production-crashing code. Either use strict assert or proper type declarations instead.
 */
final class InlineVarDocTagToAssertRector extends AbstractRector implements MinPhpVersionInterface, DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Convert inline `@var` tags to calls to `assert()`', [new CodeSample(<<<'CODE_SAMPLE'
/** @var Foo $foo */
$foo = createFoo();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$foo = createFoo();
assert($foo instanceof Foo);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Expression::class];
    }
    /**
     * @param Expression $node
     * @return Node[]|null
     */
    public function refactor(Node $node): ?array
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated as it might create production-crashing code. Either use strict assert or proper type declarations instead.', self::class));
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::STRING_IN_ASSERT_ARG;
    }
}
