<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\Enum_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use Rector\Core\Rector\AbstractRector;
use Rector\Php81\NodeFactory\ClassFromEnumFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp80\Rector\Enum_\DowngradeEnumToConstantListClassRector\DowngradeEnumToConstantListClassRectorTest
 */
final class DowngradeEnumToConstantListClassRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Php81\NodeFactory\ClassFromEnumFactory
     */
    private $classFromEnumFactory;
    public function __construct(\Rector\Php81\NodeFactory\ClassFromEnumFactory $classFromEnumFactory)
    {
        $this->classFromEnumFactory = $classFromEnumFactory;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Downgrade enum to constant list class', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
enum Direction
{
    case LEFT;

    case RIGHT;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class Direction
{
    public const LEFT = 'left';

    public const RIGHT = 'right';
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Enum_::class];
    }
    /**
     * @param Enum_ $node
     */
    public function refactor(\PhpParser\Node $node) : \PhpParser\Node\Stmt\Class_
    {
        return $this->classFromEnumFactory->createFromEnum($node);
    }
}
