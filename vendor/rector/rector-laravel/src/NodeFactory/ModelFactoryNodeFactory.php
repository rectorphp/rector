<?php

declare (strict_types=1);
namespace Rector\Laravel\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20211020\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
use RectorPrefix20211020\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder;
use RectorPrefix20211020\Symplify\Astral\ValueObject\NodeBuilder\PropertyBuilder;
final class ModelFactoryNodeFactory
{
    /**
     * @var string
     */
    private const THIS = 'this';
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \Rector\Core\PhpParser\Node\Value\ValueResolver $valueResolver, \RectorPrefix20211020\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeFactory = $nodeFactory;
        $this->valueResolver = $valueResolver;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
    }
    public function createEmptyFactory(string $name, \PhpParser\Node\Expr $expr) : \PhpParser\Node\Stmt\Class_
    {
        $class = new \PhpParser\Node\Stmt\Class_($name . 'Factory');
        $class->extends = new \PhpParser\Node\Name\FullyQualified('Illuminate\\Database\\Eloquent\\Factories\\Factory');
        $propertyBuilder = new \RectorPrefix20211020\Symplify\Astral\ValueObject\NodeBuilder\PropertyBuilder('model');
        $propertyBuilder->makeProtected();
        $propertyBuilder->setDefault($expr);
        $property = $propertyBuilder->getNode();
        $class->stmts[] = $property;
        return $class;
    }
    public function createDefinition(\PhpParser\Node\Expr\Closure $closure) : \PhpParser\Node\Stmt\ClassMethod
    {
        if (isset($closure->params[0])) {
            $this->fakerVariableToPropertyFetch($closure->stmts, $closure->params[0]);
        }
        return $this->createPublicMethod('definition', $closure->stmts);
    }
    public function createStateMethod(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        if (!isset($methodCall->args[2])) {
            return null;
        }
        if (!$methodCall->args[2] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $thirdArgValue = $methodCall->args[2]->value;
        // the third argument may be closure or array
        if ($thirdArgValue instanceof \PhpParser\Node\Expr\Closure && isset($thirdArgValue->params[0])) {
            $this->fakerVariableToPropertyFetch($thirdArgValue->stmts, $thirdArgValue->params[0]);
            unset($thirdArgValue->params[0]);
        }
        $expr = $this->nodeFactory->createMethodCall(self::THIS, 'state', [$methodCall->args[2]]);
        $return = new \PhpParser\Node\Stmt\Return_($expr);
        if (!isset($methodCall->args[1])) {
            return null;
        }
        if (!$methodCall->args[1] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        return $this->createPublicMethod($this->valueResolver->getValue($methodCall->args[1]->value), [$return]);
    }
    public function createEmptyConfigure() : \PhpParser\Node\Stmt\ClassMethod
    {
        $return = new \PhpParser\Node\Stmt\Return_(new \PhpParser\Node\Expr\Variable(self::THIS));
        return $this->createPublicMethod('configure', [$return]);
    }
    public function appendConfigure(\PhpParser\Node\Stmt\ClassMethod $classMethod, string $name, \PhpParser\Node\Expr\Closure $closure) : void
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (\PhpParser\Node $node) use($closure, $name) : ?Return_ {
            if (!$node instanceof \PhpParser\Node\Stmt\Return_) {
                return null;
            }
            if ($node->expr === null) {
                return null;
            }
            if (isset($closure->params[1])) {
                $this->fakerVariableToPropertyFetch($closure->stmts, $closure->params[1]);
                // remove argument $faker
                unset($closure->params[1]);
            }
            $node->expr = $this->nodeFactory->createMethodCall($node->expr, $name, [$closure]);
            return $node;
        });
    }
    /**
     * @param Node\Stmt[] $stmts
     */
    private function fakerVariableToPropertyFetch(array $stmts, \PhpParser\Node\Param $param) : void
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmts, function (\PhpParser\Node $node) use($param) : ?PropertyFetch {
            if (!$node instanceof \PhpParser\Node\Expr\Variable) {
                return null;
            }
            $name = $this->nodeNameResolver->getName($param->var);
            if ($name === null) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node, $name)) {
                return null;
            }
            // use $this->faker instead of $faker
            return $this->nodeFactory->createPropertyFetch(self::THIS, 'faker');
        });
    }
    /**
     * @param Node\Stmt[] $stmts
     */
    private function createPublicMethod(string $name, array $stmts) : \PhpParser\Node\Stmt\ClassMethod
    {
        $methodBuilder = new \RectorPrefix20211020\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder($name);
        $methodBuilder->makePublic();
        $methodBuilder->addStmts($stmts);
        return $methodBuilder->getNode();
    }
}
