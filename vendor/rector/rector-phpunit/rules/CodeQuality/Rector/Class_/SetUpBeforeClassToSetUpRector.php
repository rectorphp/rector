<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\Class_\SetUpBeforeClassToSetUpRector\SetUpBeforeClassToSetUpRectorTest
 */
final class SetUpBeforeClassToSetUpRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change setUpBeforeClass() to setUp() if not needed', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    private static $someService;

    public static function setUpBeforeClass(): void
    {
        self::$someService = new SomeService();
    }

    public function test()
    {
        $result = self::$someService->getValue();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    private $someService;

    protected function setUp(): void
    {
        $this->someService = new SomeService();
    }

    public function test()
    {
        $result = $this->someService->getValue();
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $className = $this->getName($node);
        if ($className === null) {
            return null;
        }
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $setUpBeforeClassMethod = $node->getMethod(MethodName::SET_UP_BEFORE_CLASS);
        if (!$setUpBeforeClassMethod instanceof ClassMethod) {
            return null;
        }
        $changedPropertyNames = [];
        // replace static property fetches
        $this->traverseNodesWithCallable($setUpBeforeClassMethod, function (Node $node) use(&$changedPropertyNames) {
            if (!$node instanceof Assign) {
                return null;
            }
            if (!$node->var instanceof StaticPropertyFetch) {
                return null;
            }
            $staticPropertyFetch = $node->var;
            if (!$this->isName($staticPropertyFetch->class, 'self')) {
                return null;
            }
            $node->var = new PropertyFetch(new Variable('this'), $staticPropertyFetch->name);
            $propertyName = $this->getName($staticPropertyFetch->name);
            if (!\is_string($propertyName)) {
                return null;
            }
            $changedPropertyNames[] = $propertyName;
        });
        if ($changedPropertyNames === []) {
            return null;
        }
        // remove static flag
        $setUpBeforeClassMethod->flags -= Class_::MODIFIER_STATIC;
        // remove public flag
        $setUpBeforeClassMethod->flags -= Class_::MODIFIER_PUBLIC;
        // make protected
        $setUpBeforeClassMethod->flags += Class_::MODIFIER_PROTECTED;
        $setUpBeforeClassMethod->name = new Identifier('setUp');
        foreach ($node->getProperties() as $property) {
            if (!$property->isStatic()) {
                continue;
            }
            if ($this->isNames($property, $changedPropertyNames)) {
                $property->flags -= Class_::MODIFIER_STATIC;
            }
        }
        // replace same property access in the class
        $this->traverseNodesWithCallable($node->getMethods(), function (Node $node) use($changedPropertyNames) : ?PropertyFetch {
            if (!$node instanceof StaticPropertyFetch) {
                return null;
            }
            if (!$this->isNames($node, $changedPropertyNames)) {
                return null;
            }
            return new PropertyFetch(new Variable('this'), $node->name);
        });
        return $node;
    }
}
