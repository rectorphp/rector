<?php

declare (strict_types=1);
namespace Rector\Laravel\Rector\Namespace_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Core\Rector\AbstractRector;
use Rector\Laravel\NodeFactory\ModelFactoryNodeFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://laravel.com/docs/7.x/database-testing#writing-factories
 * @changelog https://laravel.com/docs/8.x/database-testing#defining-model-factories
 *
 * @see \Rector\Laravel\Tests\Rector\Namespace_\FactoryDefinitionRector\FactoryDefinitionRectorTest
 */
final class FactoryDefinitionRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Laravel\NodeFactory\ModelFactoryNodeFactory
     */
    private $modelFactoryNodeFactory;
    public function __construct(ModelFactoryNodeFactory $modelFactoryNodeFactory)
    {
        $this->modelFactoryNodeFactory = $modelFactoryNodeFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Upgrade legacy factories to support classes.', [new CodeSample(<<<'CODE_SAMPLE'
use Faker\Generator as Faker;

$factory->define(App\User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
    ];
});
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Faker\Generator as Faker;

class UserFactory extends \Illuminate\Database\Eloquent\Factories\Factory
{
    protected $model = App\User::class;
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
        ];
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
        return [Namespace_::class, FileWithoutNamespace::class];
    }
    /**
     * @param Namespace_|FileWithoutNamespace $node
     */
    public function refactor(Node $node) : ?Node
    {
        $factories = [];
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            // do not refactor if factory variable changed
            if ($this->isReAssignFactoryVariable($stmt->expr)) {
                return null;
            }
            if (!$stmt->expr instanceof MethodCall) {
                continue;
            }
            if ($this->shouldSkipExpression($stmt->expr)) {
                continue;
            }
            /** @var Arg $firstArg */
            $firstArg = $stmt->expr->args[0];
            $name = $this->getNameFromClassConstFetch($firstArg->value);
            if (!$name instanceof Name) {
                continue;
            }
            if (!isset($factories[$name->toString()])) {
                $factories[$name->toString()] = $this->createFactory($name->getLast(), $firstArg->value);
            }
            $this->processFactoryConfiguration($factories[$name->toString()], $stmt->expr);
            unset($node->stmts[$key]);
        }
        if ($factories === []) {
            return null;
        }
        $node->stmts = \array_merge($node->stmts, $factories);
        return $node;
    }
    private function isReAssignFactoryVariable(Expr $expr) : bool
    {
        if (!$expr instanceof Assign) {
            return \false;
        }
        return $this->isName($expr->var, 'factory');
    }
    private function shouldSkipExpression(MethodCall $methodCall) : bool
    {
        if (!isset($methodCall->args[0])) {
            return \true;
        }
        if (!$methodCall->args[0] instanceof Arg) {
            return \true;
        }
        if (!$methodCall->args[0]->value instanceof ClassConstFetch) {
            return \true;
        }
        return !$this->isNames($methodCall->name, ['define', 'state', 'afterMaking', 'afterCreating']);
    }
    private function getNameFromClassConstFetch(Expr $expr) : ?Name
    {
        if (!$expr instanceof ClassConstFetch) {
            return null;
        }
        if ($expr->class instanceof Expr) {
            return null;
        }
        return $expr->class;
    }
    private function createFactory(string $name, Expr $expr) : Class_
    {
        return $this->modelFactoryNodeFactory->createEmptyFactory($name, $expr);
    }
    private function processFactoryConfiguration(Class_ $class, MethodCall $methodCall) : void
    {
        if (!$this->isName($methodCall->var, 'factory')) {
            return;
        }
        if ($this->isName($methodCall->name, 'define')) {
            $this->addDefinition($class, $methodCall);
        }
        if ($this->isName($methodCall->name, 'state')) {
            $this->addState($class, $methodCall);
        }
        if (!$this->isNames($methodCall->name, ['afterMaking', 'afterCreating'])) {
            return;
        }
        $name = $this->getName($methodCall->name);
        if ($name === null) {
            return;
        }
        $this->appendAfterCalling($class, $methodCall, $name);
    }
    private function addDefinition(Class_ $class, MethodCall $methodCall) : void
    {
        if (!isset($methodCall->args[1])) {
            return;
        }
        if (!$methodCall->args[1] instanceof Arg) {
            return;
        }
        $callback = $methodCall->args[1]->value;
        if (!$callback instanceof Closure) {
            return;
        }
        $class->stmts[] = $this->modelFactoryNodeFactory->createDefinition($callback);
    }
    private function addState(Class_ $class, MethodCall $methodCall) : void
    {
        $classMethod = $this->modelFactoryNodeFactory->createStateMethod($methodCall);
        if (!$classMethod instanceof ClassMethod) {
            return;
        }
        $class->stmts[] = $classMethod;
    }
    private function appendAfterCalling(Class_ $class, MethodCall $methodCall, string $name) : void
    {
        if (!isset($methodCall->args[1])) {
            return;
        }
        if (!$methodCall->args[1] instanceof Arg) {
            return;
        }
        $closure = $methodCall->args[1]->value;
        if (!$closure instanceof Closure) {
            return;
        }
        $method = $class->getMethod('configure');
        if (!$method instanceof ClassMethod) {
            $method = $this->modelFactoryNodeFactory->createEmptyConfigure();
            $class->stmts[] = $method;
        }
        $this->modelFactoryNodeFactory->appendConfigure($method, $name, $closure);
    }
}
