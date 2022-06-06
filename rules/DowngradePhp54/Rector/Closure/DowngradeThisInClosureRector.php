<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DowngradePhp54\Rector\Closure;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\Closure;
use RectorPrefix20220606\PhpParser\Node\Expr\ClosureUse;
use RectorPrefix20220606\PhpParser\Node\Expr\PropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\FunctionLike;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
use RectorPrefix20220606\PhpParser\Node\Stmt\Function_;
use RectorPrefix20220606\PHPStan\Reflection\Php\PhpPropertyReflection;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\NamedVariableFactory;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\Core\Reflection\ReflectionResolver;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
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
    public function __construct(NamedVariableFactory $namedVariableFactory, ReflectionResolver $reflectionResolver)
    {
        $this->namedVariableFactory = $namedVariableFactory;
        $this->reflectionResolver = $reflectionResolver;
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
        $this->nodesToAddCollector->addNodeBeforeNode($expression, $node, $this->file->getSmartFileInfo());
        $this->traverseNodesWithCallable($node, function (Node $subNode) use($selfVariable) : ?Closure {
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
            $parent = $this->betterNodeFinder->findParentByTypes($subNode, [ClassMethod::class, Function_::class]);
            if ($parent instanceof FunctionLike && $parent !== $closureParentFunctionLike) {
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
