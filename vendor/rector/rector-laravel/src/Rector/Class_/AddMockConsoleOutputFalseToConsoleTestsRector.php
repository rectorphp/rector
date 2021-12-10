<?php

declare (strict_types=1);
namespace Rector\Laravel\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeManipulator\SetUpClassMethodNodeManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/laravel/framework/issues/26450#issuecomment-449401202
 * @see https://github.com/laravel/framework/commit/055fe52dbb7169dc51bd5d5deeb05e8da9be0470#diff-76a649cb397ea47f5613459c335f88c1b68e5f93e51d46e9fb5308ec55ded221
 *
 * @see \Rector\Laravel\Tests\Rector\Class_\AddMockConsoleOutputFalseToConsoleTestsRector\AddMockConsoleOutputFalseToConsoleTestsRectorTest
 */
final class AddMockConsoleOutputFalseToConsoleTestsRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    /**
     * @var \Rector\PHPUnit\NodeManipulator\SetUpClassMethodNodeManipulator
     */
    private $setUpClassMethodNodeManipulator;
    public function __construct(\Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer $propertyFetchAnalyzer, \Rector\PHPUnit\NodeManipulator\SetUpClassMethodNodeManipulator $setUpClassMethodNodeManipulator)
    {
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->setUpClassMethodNodeManipulator = $setUpClassMethodNodeManipulator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add "$this->mockConsoleOutput = false"; to console tests that work with output content', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\TestCase;

final class SomeTest extends TestCase
{
    public function test(): void
    {
        $this->assertEquals('content', \trim((new Artisan())::output()));
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\TestCase;

final class SomeTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->mockConsoleOutput = false;
    }

    public function test(): void
    {
        $this->assertEquals('content', \trim((new Artisan())::output()));
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
        if (!$this->isObjectType($node, new \PHPStan\Type\ObjectType('Illuminate\\Foundation\\Testing\\TestCase'))) {
            return null;
        }
        if (!$this->isTestingConsoleOutput($node)) {
            return null;
        }
        // has setUp with property `$mockConsoleOutput = false`
        if ($this->hasMockConsoleOutputFalse($node)) {
            return null;
        }
        $assign = $this->createAssign();
        $this->setUpClassMethodNodeManipulator->decorateOrCreate($node, [$assign]);
        return $node;
    }
    private function isTestingConsoleOutput(\PhpParser\Node\Stmt\Class_ $class) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($class->stmts, function (\PhpParser\Node $node) : bool {
            if (!$node instanceof \PhpParser\Node\Expr\StaticCall) {
                return \false;
            }
            $callerType = $this->nodeTypeResolver->getType($node->class);
            if (!$callerType->isSuperTypeOf(new \PHPStan\Type\ObjectType('Illuminate\\Support\\Facades\\Artisan'))->yes()) {
                return \false;
            }
            return $this->isName($node->name, 'output');
        });
    }
    private function hasMockConsoleOutputFalse(\PhpParser\Node\Stmt\Class_ $class) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($class, function (\PhpParser\Node $node) : bool {
            if ($node instanceof \PhpParser\Node\Expr\Assign) {
                if (!$this->propertyFetchAnalyzer->isLocalPropertyFetchName($node->var, 'mockConsoleOutput')) {
                    return \false;
                }
                return $this->valueResolver->isFalse($node->expr);
            }
            return \false;
        });
    }
    private function createAssign() : \PhpParser\Node\Expr\Assign
    {
        $propertyFetch = new \PhpParser\Node\Expr\PropertyFetch(new \PhpParser\Node\Expr\Variable('this'), 'mockConsoleOutput');
        return new \PhpParser\Node\Expr\Assign($propertyFetch, $this->nodeFactory->createFalse());
    }
}
