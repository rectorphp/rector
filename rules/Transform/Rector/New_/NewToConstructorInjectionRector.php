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
use RectorPrefix202208\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\New_\NewToConstructorInjectionRector\NewToConstructorInjectionRectorTest
 */
final class NewToConstructorInjectionRector extends AbstractRector implements ConfigurableRectorInterface
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
    public function __construct(PropertyFetchFactory $propertyFetchFactory, PropertyNaming $propertyNaming, PropertyToAddCollector $propertyToAddCollector)
    {
        $this->propertyFetchFactory = $propertyFetchFactory;
        $this->propertyNaming = $propertyNaming;
        $this->propertyToAddCollector = $propertyToAddCollector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change defined new type to constructor injection', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
        return [New_::class, Assign::class, MethodCall::class, Expression::class];
    }
    /**
     * @param New_|Assign|MethodCall|Expression $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof MethodCall) {
            return $this->refactorMethodCall($node);
        }
        if ($node instanceof Expression) {
            $nodeExpr = $node->expr;
            if ($nodeExpr instanceof Assign) {
                $this->refactorAssignExpression($node, $nodeExpr);
            }
        }
        if ($node instanceof New_) {
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
        Assert::allString($typesToConstructorInjections);
        foreach ($typesToConstructorInjections as $typeToConstructorInjection) {
            $this->constructorInjectionObjectTypes[] = new ObjectType($typeToConstructorInjection);
        }
    }
    private function refactorMethodCall(MethodCall $methodCall) : ?MethodCall
    {
        foreach ($this->constructorInjectionObjectTypes as $constructorInjectionObjectType) {
            if (!$methodCall->var instanceof Variable) {
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
    private function refactorAssignExpression(Expression $expression, Assign $assign) : void
    {
        $currentAssign = $assign->expr instanceof Assign ? $assign->expr : $assign;
        if (!$currentAssign->expr instanceof New_) {
            return;
        }
        foreach ($this->constructorInjectionObjectTypes as $constructorInjectionObjectType) {
            if (!$this->isObjectType($currentAssign->expr, $constructorInjectionObjectType)) {
                continue;
            }
            $this->removeNode($expression);
        }
    }
    private function refactorNew(New_ $new) : void
    {
        foreach ($this->constructorInjectionObjectTypes as $constructorInjectionObjectType) {
            if (!$this->isObjectType($new->class, $constructorInjectionObjectType)) {
                continue;
            }
            $classLike = $this->betterNodeFinder->findParentType($new, Class_::class);
            if (!$classLike instanceof Class_) {
                continue;
            }
            $expectedPropertyName = $this->propertyNaming->getExpectedNameFromType($constructorInjectionObjectType);
            if (!$expectedPropertyName instanceof ExpectedName) {
                continue;
            }
            $propertyMetadata = new PropertyMetadata($expectedPropertyName->getName(), $constructorInjectionObjectType, Class_::MODIFIER_PRIVATE);
            $this->propertyToAddCollector->addPropertyToClass($classLike, $propertyMetadata);
        }
    }
}
