<?php

declare(strict_types=1);

namespace Rector\Transform\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\PropertyNaming;
use Rector\Naming\ValueObject\ExpectedName;
use Rector\NodeRemoval\AssignRemover;
use Rector\PostRector\Collector\PropertyToAddCollector;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Rector\Transform\NodeFactory\PropertyFetchFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Tests\Transform\Rector\New_\NewToConstructorInjectionRector\NewToConstructorInjectionRectorTest
 */
final class NewToConstructorInjectionRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    final public const TYPES_TO_CONSTRUCTOR_INJECTION = 'types_to_constructor_injection';

    /**
     * @var ObjectType[]
     */
    private array $constructorInjectionObjectTypes = [];

    public function __construct(
        private readonly PropertyFetchFactory $propertyFetchFactory,
        private readonly PropertyNaming $propertyNaming,
        private readonly PropertyToAddCollector $propertyToAddCollector,
        private readonly AssignRemover $assignRemover
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change defined new type to constructor injection', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $validator = new Validator();
        $validator->validate(1000);
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
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
                ,
                ['Validator']
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [New_::class, Assign::class, MethodCall::class];
    }

    /**
     * @param New_|Assign|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof MethodCall) {
            return $this->refactorMethodCall($node);
        }

        if ($node instanceof Assign) {
            $this->refactorAssign($node);
        }

        if ($node instanceof New_) {
            $this->refactorNew($node);
        }

        return null;
    }

    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        $typesToConstructorInjections = $configuration[self::TYPES_TO_CONSTRUCTOR_INJECTION] ?? $configuration;
        Assert::isArray($typesToConstructorInjections);

        foreach ($typesToConstructorInjections as $typeToConstructorInjection) {
            $this->constructorInjectionObjectTypes[] = new ObjectType($typeToConstructorInjection);
        }
    }

    private function refactorMethodCall(MethodCall $methodCall): ?MethodCall
    {
        foreach ($this->constructorInjectionObjectTypes as $constructorInjectionObjectType) {
            if (! $methodCall->var instanceof Variable) {
                continue;
            }

            if (! $this->isObjectType($methodCall->var, $constructorInjectionObjectType)) {
                continue;
            }

            if (! $this->nodeTypeResolver->isObjectType($methodCall->var, $constructorInjectionObjectType)) {
                continue;
            }

            $methodCall->var = $this->propertyFetchFactory->createFromType($constructorInjectionObjectType);
            return $methodCall;
        }

        return null;
    }

    private function refactorAssign(Assign $assign): void
    {
        if (! $assign->expr instanceof New_) {
            return;
        }

        foreach ($this->constructorInjectionObjectTypes as $constructorInjectionObjectType) {
            if (! $this->isObjectType($assign->expr, $constructorInjectionObjectType)) {
                continue;
            }

            $this->assignRemover->removeAssignNode($assign);
        }
    }

    private function refactorNew(New_ $new): void
    {
        foreach ($this->constructorInjectionObjectTypes as $constructorInjectionObjectType) {
            if (! $this->isObjectType($new->class, $constructorInjectionObjectType)) {
                continue;
            }

            $classLike = $this->betterNodeFinder->findParentType($new, Class_::class);
            if (! $classLike instanceof Class_) {
                continue;
            }

            $expectedPropertyName = $this->propertyNaming->getExpectedNameFromType($constructorInjectionObjectType);
            if (! $expectedPropertyName instanceof ExpectedName) {
                continue;
            }

            $propertyMetadata = new PropertyMetadata(
                $expectedPropertyName->getName(),
                $constructorInjectionObjectType,
                Class_::MODIFIER_PRIVATE
            );
            $this->propertyToAddCollector->addPropertyToClass($classLike, $propertyMetadata);
        }
    }
}
