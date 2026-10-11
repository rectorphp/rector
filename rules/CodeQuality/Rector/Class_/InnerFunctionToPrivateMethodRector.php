<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * @deprecated This rule is deprecated, as moving an inner named function to a private method is a niche change that can alter behavior in undesired ways and needs a human check.
 */
final class InnerFunctionToPrivateMethodRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Turn an inner named function declared inside a class method into a private method, as inner named functions are not supported by PHPStan', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(): void
    {
        function inner(): void
        {
            echo 'hello';
        }

        inner();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(): void
    {
        $this->inner();
    }

    private function inner(): void
    {
        echo 'hello';
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
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated, as it is a niche change that can alter behavior in undesired ways and needs a human check', self::class));
    }
}
