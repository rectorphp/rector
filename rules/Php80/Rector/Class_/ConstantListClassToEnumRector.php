<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Rector\AbstractRector;
use Rector\Php80\NodeAnalyzer\EnumConstListClassDetector;
use Rector\Php81\NodeFactory\EnumFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php80\Rector\Class_\ConstantListClassToEnumRector\ConstantListClassToEnumRectorTest
 */
final class ConstantListClassToEnumRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\EnumConstListClassDetector
     */
    private $enumConstListClassDetector;
    /**
     * @readonly
     * @var \Rector\Php81\NodeFactory\EnumFactory
     */
    private $enumFactory;
    public function __construct(\Rector\Php80\NodeAnalyzer\EnumConstListClassDetector $enumConstListClassDetector, \Rector\Php81\NodeFactory\EnumFactory $enumFactory)
    {
        $this->enumConstListClassDetector = $enumConstListClassDetector;
        $this->enumFactory = $enumFactory;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Upgrade constant list classes to full blown enum', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class Direction
{
    public const LEFT = 'left';

    public const RIGHT = 'right';
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
enum Direction
{
    case LEFT;

    case RIGHT;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->enumConstListClassDetector->detect($node)) {
            return null;
        }
        return $this->enumFactory->createFromClass($node);
    }
}
