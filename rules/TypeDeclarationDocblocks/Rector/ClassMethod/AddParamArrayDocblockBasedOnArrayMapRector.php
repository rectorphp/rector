<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated This rule is deprecated, as a single array_map() closure only proves what one call site reads. The param type is vague and unreliable. Add the @param docblock manually instead.
 */
final class AddParamArrayDocblockBasedOnArrayMapRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add @param array docblock if array_map is used on the parameter', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(array $names): void
    {
        $names = array_map(fn(string $name) => trim($name), $names);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @param string[] $names
     */
    public function run(array $names): void
    {
        $names = array_map(fn(string $name) => trim($name), $names);
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
        return [ClassMethod::class, Function_::class];
    }
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated, as the param type guessed from a single array_map() closure is vague and unreliable. Add the @param docblock manually instead', self::class));
    }
}
