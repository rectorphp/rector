<?php

declare (strict_types=1);
namespace Rector\DowngradePhp71\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use Rector\Core\Rector\AbstractRector;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/class_const_visibility
 *
 * @see \Rector\Tests\DowngradePhp71\Rector\ClassConst\DowngradeClassConstantVisibilityRectorTest
 */
final class DowngradeClassConstantVisibilityRector extends AbstractRector
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
        return new RuleDefinition('Downgrade class constant visibility', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
   public const PUBLIC_CONST_B = 2;
   protected const PROTECTED_CONST = 3;
   private const PRIVATE_CONST = 4;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
   const PUBLIC_CONST_B = 2;
   const PROTECTED_CONST = 3;
   const PRIVATE_CONST = 4;
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
    public function refactor(Node $node) : ClassConst
    {
        $this->visibilityManipulator->removeVisibility($node);
        return $node;
    }
}
