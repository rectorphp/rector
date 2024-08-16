<?php

declare (strict_types=1);
namespace Rector\Php71\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php71\Rector\ClassConst\PublicConstantVisibilityRector\PublicConstantVisibilityRectorTest
 * @deprecated Since 1.2.4, as adds blindly public to all constants, even local-only ones that should be private. Use scope-based solution instead, e.g. https://tomasvotruba.com/blog/how-to-add-visbility-to-338-class-constants-in-25-seconds
 */
final class PublicConstantVisibilityRector extends AbstractRector implements MinPhpVersionInterface, DeprecatedInterface
{
    /**
     * @readonly
     * @var \Rector\Privatization\NodeManipulator\VisibilityManipulator
     */
    private $visibilityManipulator;
    public function __construct(VisibilityManipulator $visibilityManipulator)
    {
        $this->visibilityManipulator = $visibilityManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add explicit public constant visibility.', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    const HEY = 'you';
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public const HEY = 'you';
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassConst::class];
    }
    /**
     * @param ClassConst $node
     */
    public function refactor(Node $node) : ?Node
    {
        return $this->visibilityManipulator->publicize($node);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::CONSTANT_VISIBILITY;
    }
}
