<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\NodeManipulator\ClassInsertManipulator;
use Rector\Core\NodeManipulator\ClassManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/sebastianbergmann/phpunit/issues/4142
 * @see https://github.com/sebastianbergmann/phpunit/issues/4141
 * @see https://github.com/sebastianbergmann/phpunit/issues/4149
 *
 * @see \Rector\PHPUnit\Tests\Rector\Class_\AddProphecyTraitRector\AddProphecyTraitRectorTest
 */
final class AddProphecyTraitRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const PROPHECY_TRAIT = 'Prophecy\\PhpUnit\\ProphecyTrait';
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ClassInsertManipulator
     */
    private $classInsertManipulator;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ClassManipulator
     */
    private $classManipulator;
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(\Rector\Core\NodeManipulator\ClassInsertManipulator $classInsertManipulator, \Rector\Core\NodeManipulator\ClassManipulator $classManipulator, \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->classInsertManipulator = $classInsertManipulator;
        $this->classManipulator = $classManipulator;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add Prophecy trait for method using $this->prophesize()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class ExampleTest extends TestCase
{
    public function testOne(): void
    {
        $prophecy = $this->prophesize(\AnInterface::class);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class ExampleTest extends TestCase
{
    use ProphecyTrait;

    public function testOne(): void
    {
        $prophecy = $this->prophesize(\AnInterface::class);
    }
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
        if ($this->shouldSkipClass($node)) {
            return null;
        }
        $this->classInsertManipulator->addAsFirstTrait($node, self::PROPHECY_TRAIT);
        return $node;
    }
    private function shouldSkipClass(\PhpParser\Node\Stmt\Class_ $class) : bool
    {
        $hasProphesizeMethodCall = (bool) $this->betterNodeFinder->findFirst($class, function (\PhpParser\Node $node) : bool {
            return $this->testsNodeAnalyzer->isAssertMethodCallName($node, 'prophesize');
        });
        if (!$hasProphesizeMethodCall) {
            return \true;
        }
        return $this->classManipulator->hasTrait($class, self::PROPHECY_TRAIT);
    }
}
