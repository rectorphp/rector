<?php

declare (strict_types=1);
namespace Rector\DowngradePhp54\Rector\Closure;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use Rector\Core\PhpParser\Node\NamedVariableFactory;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/closures/object-extension
 *
 * @see \Rector\Tests\DowngradePhp54\Rector\Closure\DowngradeThisInClosureRector\DowngradeThisInClosureRectorTest
 */
final class DowngradeThisInClosureRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NamedVariableFactory
     */
    private $namedVariableFactory;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(\Rector\Core\PhpParser\Node\NamedVariableFactory $namedVariableFactory, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver)
    {
        $this->namedVariableFactory = $namedVariableFactory;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Downgrade $this-> inside Closure to use assigned $self = $this before Closure', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public $property = 'test';

    public function run()
    {
        $function = function () {
            echo $this->property;
        };

        $function();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public $property = 'test';

    public function run()
    {
        $self = $this;
        $function = function () use ($self) {
            echo $self->property;
        };

        $function();
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
        return [\PhpParser\Node\Expr\Closure::class];
    }
    /**
     * @param Closure $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $closureParentFunctionLike = $this->betterNodeFinder->findParentByTypes($node, [\PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Function_::class]);
        /** @var PropertyFetch[] $propertyFetches */
        $propertyFetches = $this->resolvePropertyFetches($node, $closureParentFunctionLike);
        if ($propertyFetches === []) {
            return null;
        }
        $selfVariable = $this->namedVariableFactory->createVariable($node, 'self');
        $expression = new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign($selfVariable, new \PhpParser\Node\Expr\Variable('this')));
        $this->nodesToAddCollector->addNodeBeforeNode($expression, $node);
        $this->traverseNodesWithCallable($node, function (\PhpParser\Node $subNode) use($selfVariable) : ?Closure {
            if (!$subNode instanceof \PhpParser\Node\Expr\Closure) {
                return null;
            }
            $subNode->uses = \array_merge($subNode->uses, [new \PhpParser\Node\Expr\ClosureUse($selfVariable)]);
            return $subNode;
        });
        foreach ($propertyFetches as $propertyFetch) {
            $propertyFetch->var = $selfVariable;
        }
        return $node;
    }
    /**
     * @return PropertyFetch[]
     */
    private function resolvePropertyFetches(\PhpParser\Node\Expr\Closure $node, ?\PhpParser\Node\FunctionLike $closureParentFunctionLike) : array
    {
        /** @var PropertyFetch[] $propertyFetches */
        $propertyFetches = $this->betterNodeFinder->find($node->stmts, function (\PhpParser\Node $subNode) use($closureParentFunctionLike) : bool {
            // multiple deep Closure may access $this, unless its parent is not Closure
            $parent = $this->betterNodeFinder->findParentByTypes($subNode, [\PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Function_::class]);
            if ($parent instanceof \PhpParser\Node\FunctionLike && $parent !== $closureParentFunctionLike) {
                return \false;
            }
            if (!$subNode instanceof \PhpParser\Node\Expr\PropertyFetch) {
                return \false;
            }
            if (!$subNode->var instanceof \PhpParser\Node\Expr\Variable) {
                return \false;
            }
            if (!$this->nodeNameResolver->isName($subNode->var, 'this')) {
                return \false;
            }
            $phpPropertyReflection = $this->reflectionResolver->resolvePropertyReflectionFromPropertyFetch($subNode);
            if (!$phpPropertyReflection instanceof \PHPStan\Reflection\Php\PhpPropertyReflection) {
                return \false;
            }
            return $phpPropertyReflection->isPublic();
        });
        return $propertyFetches;
    }
}
