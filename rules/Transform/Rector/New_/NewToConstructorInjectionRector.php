<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\PropertyNaming;
use Rector\Naming\ValueObject\ExpectedName;
use Rector\PostRector\Collector\PropertyToAddCollector;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Rector\Transform\NodeFactory\PropertyFetchFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220531\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\New_\NewToConstructorInjectionRector\NewToConstructorInjectionRectorTest
 */
final class NewToConstructorInjectionRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var ObjectType[]
     */
    private $constructorInjectionObjectTypes = [];
    /**
     * @readonly
     * @var \Rector\Transform\NodeFactory\PropertyFetchFactory
     */
    private $propertyFetchFactory;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\PropertyToAddCollector
     */
    private $propertyToAddCollector;
    public function __construct(\Rector\Transform\NodeFactory\PropertyFetchFactory $propertyFetchFactory, \Rector\Naming\Naming\PropertyNaming $propertyNaming, \Rector\PostRector\Collector\PropertyToAddCollector $propertyToAddCollector)
    {
        $this->propertyFetchFactory = $propertyFetchFactory;
        $this->propertyNaming = $propertyNaming;
        $this->propertyToAddCollector = $propertyToAddCollector;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change defined new type to constructor injection', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $validator = new Validator();
        $validator->validate(1000);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var Validator
     */
    private $validator;

    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    public function run()
    {
        $this->validator->validate(1000);
    }
}
CODE_SAMPLE
, ['Validator'])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\New_::class, \PhpParser\Node\Expr\Assign::class, \PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Stmt\Expression::class];
    }
    /**
     * @param New_|Assign|MethodCall|Expression $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Expr\MethodCall) {
            return $this->refactorMethodCall($node);
        }
        if ($node instanceof \PhpParser\Node\Stmt\Expression) {
            $nodeExpr = $node->expr;
            if ($nodeExpr instanceof \PhpParser\Node\Expr\Assign) {
                $this->refactorAssignExpression($node, $nodeExpr);
            }
        }
        if ($node instanceof \PhpParser\Node\Expr\New_) {
            $this->refactorNew($node);
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $typesToConstructorInjections = $configuration;
        \RectorPrefix20220531\Webmozart\Assert\Assert::allString($typesToConstructorInjections);
        foreach ($typesToConstructorInjections as $typeToConstructorInjection) {
            $this->constructorInjectionObjectTypes[] = new \PHPStan\Type\ObjectType($typeToConstructorInjection);
        }
    }
    private function refactorMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node\Expr\MethodCall
    {
        foreach ($this->constructorInjectionObjectTypes as $constructorInjectionObjectType) {
            if (!$methodCall->var instanceof \PhpParser\Node\Expr\Variable) {
                continue;
            }
            if (!$this->isObjectType($methodCall->var, $constructorInjectionObjectType)) {
                continue;
            }
            if (!$this->nodeTypeResolver->isObjectType($methodCall->var, $constructorInjectionObjectType)) {
                continue;
            }
            $methodCall->var = $this->propertyFetchFactory->createFromType($constructorInjectionObjectType);
            return $methodCall;
        }
        return null;
    }
    private function refactorAssignExpression(\PhpParser\Node\Stmt\Expression $expression, \PhpParser\Node\Expr\Assign $assign) : void
    {
        $currentAssign = $assign->expr instanceof \PhpParser\Node\Expr\Assign ? $assign->expr : $assign;
        if (!$currentAssign->expr instanceof \PhpParser\Node\Expr\New_) {
            return;
        }
        foreach ($this->constructorInjectionObjectTypes as $constructorInjectionObjectType) {
            if (!$this->isObjectType($currentAssign->expr, $constructorInjectionObjectType)) {
                continue;
            }
            $this->removeNode($expression);
        }
    }
    private function refactorNew(\PhpParser\Node\Expr\New_ $new) : void
    {
        foreach ($this->constructorInjectionObjectTypes as $constructorInjectionObjectType) {
            if (!$this->isObjectType($new->class, $constructorInjectionObjectType)) {
                continue;
            }
            $classLike = $this->betterNodeFinder->findParentType($new, \PhpParser\Node\Stmt\Class_::class);
            if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
                continue;
            }
            $expectedPropertyName = $this->propertyNaming->getExpectedNameFromType($constructorInjectionObjectType);
            if (!$expectedPropertyName instanceof \Rector\Naming\ValueObject\ExpectedName) {
                continue;
            }
            $propertyMetadata = new \Rector\PostRector\ValueObject\PropertyMetadata($expectedPropertyName->getName(), $constructorInjectionObjectType, \PhpParser\Node\Stmt\Class_::MODIFIER_PRIVATE);
            $this->propertyToAddCollector->addPropertyToClass($classLike, $propertyMetadata);
        }
    }
}
