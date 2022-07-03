<?php

declare (strict_types=1);
namespace Rector\Laravel\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\PHPUnit\NodeAnalyzer\SetUpMethodDecorator;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/laravel/framework/issues/26450#issuecomment-449401202
 * @changelog https://github.com/laravel/framework/commit/055fe52dbb7169dc51bd5d5deeb05e8da9be0470#diff-76a649cb397ea47f5613459c335f88c1b68e5f93e51d46e9fb5308ec55ded221
 *
 * @see \Rector\Laravel\Tests\Rector\Class_\AddMockConsoleOutputFalseToConsoleTestsRector\AddMockConsoleOutputFalseToConsoleTestsRectorTest
 */
final class AddMockConsoleOutputFalseToConsoleTestsRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\SetUpMethodDecorator
     */
    private $setUpMethodDecorator;
    /**
     * @readonly
     * @var \Rector\Privatization\NodeManipulator\VisibilityManipulator
     */
    private $visibilityManipulator;
    public function __construct(PropertyFetchAnalyzer $propertyFetchAnalyzer, SetUpMethodDecorator $setUpMethodDecorator, VisibilityManipulator $visibilityManipulator)
    {
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->setUpMethodDecorator = $setUpMethodDecorator;
        $this->visibilityManipulator = $visibilityManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add "$this->mockConsoleOutput = false"; to console tests that work with output content', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isObjectType($node, new ObjectType('Illuminate\\Foundation\\Testing\\TestCase'))) {
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
        $setUpClassMethod = $node->getMethod(MethodName::SET_UP);
        if (!$setUpClassMethod instanceof ClassMethod) {
            $setUpClassMethod = new ClassMethod(MethodName::SET_UP);
            $setUpClassMethod->stmts = [new Expression(new StaticCall(new Name(ObjectReference::PARENT), MethodName::SET_UP)), new Expression($assign)];
            $this->setUpMethodDecorator->decorate($setUpClassMethod);
            $this->visibilityManipulator->makeProtected($setUpClassMethod);
            $node->stmts = \array_merge([$setUpClassMethod], $node->stmts);
        } else {
            $setUpClassMethod->stmts = \array_merge((array) $setUpClassMethod->stmts, [new Expression($assign)]);
        }
        return $node;
    }
    private function isTestingConsoleOutput(Class_ $class) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($class->stmts, function (Node $node) : bool {
            if (!$node instanceof StaticCall) {
                return \false;
            }
            $callerType = $this->nodeTypeResolver->getType($node->class);
            if (!$callerType->isSuperTypeOf(new ObjectType('Illuminate\\Support\\Facades\\Artisan'))->yes()) {
                return \false;
            }
            return $this->isName($node->name, 'output');
        });
    }
    private function hasMockConsoleOutputFalse(Class_ $class) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($class, function (Node $node) : bool {
            if ($node instanceof Assign) {
                if (!$this->propertyFetchAnalyzer->isLocalPropertyFetchName($node->var, 'mockConsoleOutput')) {
                    return \false;
                }
                return $this->valueResolver->isFalse($node->expr);
            }
            return \false;
        });
    }
    private function createAssign() : Assign
    {
        $propertyFetch = new PropertyFetch(new Variable('this'), 'mockConsoleOutput');
        return new Assign($propertyFetch, $this->nodeFactory->createFalse());
    }
}
