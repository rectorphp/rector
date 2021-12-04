<?php

declare (strict_types=1);
namespace Rector\PhpSpecToPHPUnit\Rector\Variable;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use Rector\PhpSpecToPHPUnit\PhpSpecMockCollector;
use Rector\PhpSpecToPHPUnit\Rector\AbstractPhpSpecToPHPUnitRector;
/**
 * $mock->call()
 * ↓
 * $this->mock->call()
 *
 * @see \Rector\Tests\PhpSpecToPHPUnit\Rector\Variable\PhpSpecToPHPUnitRector\PhpSpecToPHPUnitRectorTest
 */
final class MockVariableToPropertyFetchRector extends \Rector\PhpSpecToPHPUnit\Rector\AbstractPhpSpecToPHPUnitRector
{
    /**
     * @readonly
     * @var \Rector\PhpSpecToPHPUnit\PhpSpecMockCollector
     */
    private $phpSpecMockCollector;
    public function __construct(\Rector\PhpSpecToPHPUnit\PhpSpecMockCollector $phpSpecMockCollector)
    {
        $this->phpSpecMockCollector = $phpSpecMockCollector;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\Variable::class];
    }
    /**
     * @param Variable $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $class = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\Class_::class);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return null;
        }
        if (!$this->isInPhpSpecBehavior($class)) {
            return null;
        }
        if (!$this->phpSpecMockCollector->isVariableMockInProperty($class, $node)) {
            return null;
        }
        /** @var string $variableName */
        $variableName = $this->getName($node);
        return new \PhpParser\Node\Expr\PropertyFetch(new \PhpParser\Node\Expr\Variable('this'), $variableName);
    }
}
