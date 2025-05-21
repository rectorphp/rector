<?php

declare (strict_types=1);
namespace Rector\DowngradePhp83\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\DowngradePhp82\NodeManipulator\DowngradeReadonlyClassManipulator;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://www.php.net/manual/en/migration83.new-features.php#migration83.new-features.core.readonly-modifier-improvements
 * @see \Rector\Tests\DowngradePhp83\Rector\Class_\DowngradeReadonlyAnonymousClassRector\DowngradeReadonlyAnonymousClassRectorTest
 */
final class DowngradeReadonlyAnonymousClassRector extends AbstractRector
{
    /**
     * @readonly
     */
    private DowngradeReadonlyClassManipulator $downgradeReadonlyClassManipulator;
    public function __construct(DowngradeReadonlyClassManipulator $downgradeReadonlyClassManipulator)
    {
        $this->downgradeReadonlyClassManipulator = $downgradeReadonlyClassManipulator;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove "readonly" class type on anonymous class, decorate all properties to "readonly"', [new CodeSample(<<<'CODE_SAMPLE'
new readonly class
{
    public string $foo;

    public function __construct()
    {
        $this->foo = 'foo';
    }
};
CODE_SAMPLE
, <<<'CODE_SAMPLE'
new class
{
    public readonly string $foo;

    public function __construct()
    {
        $this->foo = 'foo';
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$node->isAnonymous()) {
            return null;
        }
        return $this->downgradeReadonlyClassManipulator->process($node);
    }
}
