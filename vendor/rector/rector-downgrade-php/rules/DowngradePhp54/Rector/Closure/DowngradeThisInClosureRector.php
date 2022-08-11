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
use Rector\PostRector\Collector\NodesToAddCollector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/closures/object-extension
 *
 * @see \Rector\Tests\DowngradePhp54\Rector\Closure\DowngradeThisInClosureRector\DowngradeThisInClosureRectorTest
 */
final class DowngradeThisInClosureRector extends AbstractRector
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
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\NodesToAddCollector
     */
    private $nodesToAddCollector;
    public function __construct(NamedVariableFactory $namedVariableFactory, ReflectionResolver $reflectionResolver, NodesToAddCollector $nodesToAddCollector)
    {
        $this->namedVariableFactory = $namedVariableFactory;
        $this->reflectionResolver = $reflectionResolver;
        $this->nodesToAddCollector = $nodesToAddCollector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Downgrade $this-> inside Closure to use assigned $self = $this before Closure', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Closure::class];
    }
    /**
     * @param Closure $node
     */
    public function refactor(Node $node) : ?Node
    {
        $closureParentFunctionLike = $this->betterNodeFinder->findParentByTypes($node, [ClassMethod::class, Function_::class]);
        /** @var PropertyFetch[] $propertyFetches */
        $propertyFetches = $this->resolvePropertyFetches($node, $closureParentFunctionLike);
        if ($propertyFetches === []) {
            return null;
        }
        $selfVariable = $this->namedVariableFactory->createVariable($node, 'self');
        $expression = new Expression(new Assign($selfVariable, new Variable('this')));
        $this->nodesToAddCollector->addNodeBeforeNode($expression, $node);
        $this->traverseNodesWithCallable($node, static function (Node $subNode) use($selfVariable) : ?Closure {
            if (!$subNode instanceof Closure) {
                return null;
            }
            $subNode->uses = \array_merge($subNode->uses, [new ClosureUse($selfVariable)]);
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
    private function resolvePropertyFetches(Closure $node, ?FunctionLike $closureParentFunctionLike) : array
    {
        /** @var PropertyFetch[] $propertyFetches */
        $propertyFetches = $this->betterNodeFinder->find($node->stmts, function (Node $subNode) use($closureParentFunctionLike) : bool {
            // multiple deep Closure may access $this, unless its parent is not Closure
            $parentNode = $this->betterNodeFinder->findParentByTypes($subNode, [ClassMethod::class, Function_::class]);
            if ($parentNode instanceof FunctionLike && $parentNode !== $closureParentFunctionLike) {
                return \false;
            }
            if (!$subNode instanceof PropertyFetch) {
                return \false;
            }
            if (!$subNode->var instanceof Variable) {
                return \false;
            }
            if (!$this->nodeNameResolver->isName($subNode->var, 'this')) {
                return \false;
            }
            $phpPropertyReflection = $this->reflectionResolver->resolvePropertyReflectionFromPropertyFetch($subNode);
            if (!$phpPropertyReflection instanceof PhpPropertyReflection) {
                return \false;
            }
            return $phpPropertyReflection->isPublic();
        });
        return $propertyFetches;
    }
}
