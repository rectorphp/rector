<?php

declare(strict_types=1);

namespace Rector\Restoration\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\Restoration\Rector\New_\CompleteMissingDependencyInNewRector\CompleteMissingDependencyInNewRectorTest
 */
final class CompleteMissingDependencyInNewRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const CLASS_TO_INSTANTIATE_BY_TYPE = 'class_to_instantiate_by_type';

    /**
     * @var array<class-string, class-string>
     */
    private $classToInstantiateByType = [];

    public function __construct(
        private ReflectionProvider $reflectionProvider
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Complete missing constructor dependency instance by type', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        $valueObject = new RandomValueObject();
    }
}

class RandomValueObject
{
    public function __construct(RandomDependency $randomDependency)
    {
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        $valueObject = new RandomValueObject(new RandomDependency());
    }
}

class RandomValueObject
{
    public function __construct(RandomDependency $randomDependency)
    {
    }
}
CODE_SAMPLE
                ,
                [
                    self::CLASS_TO_INSTANTIATE_BY_TYPE => [
                        'RandomDependency' => 'RandomDependency',
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [New_::class];
    }

    /**
     * @param New_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkipNew($node)) {
            return null;
        }

        /** @var ReflectionMethod $constructorMethodReflection */
        $constructorMethodReflection = $this->getNewNodeClassConstructorMethodReflection($node);

        foreach ($constructorMethodReflection->getParameters() as $position => $reflectionParameter) {
            // argument is already set
            if (isset($node->args[$position])) {
                continue;
            }

            $classToInstantiate = $this->resolveClassToInstantiateByParameterReflection($reflectionParameter);
            if ($classToInstantiate === null) {
                continue;
            }

            $new = new New_(new FullyQualified($classToInstantiate));
            $node->args[$position] = new Arg($new);
        }

        return $node;
    }

    /**
     * @param array<string, array<class-string, class-string>> $configuration
     */
    public function configure(array $configuration): void
    {
        $this->classToInstantiateByType = $configuration[self::CLASS_TO_INSTANTIATE_BY_TYPE] ?? [];
    }

    private function shouldSkipNew(New_ $new): bool
    {
        $constructorMethodReflection = $this->getNewNodeClassConstructorMethodReflection($new);
        if (! $constructorMethodReflection instanceof ReflectionMethod) {
            return true;
        }

        return $constructorMethodReflection->getNumberOfRequiredParameters() <= count($new->args);
    }

    private function getNewNodeClassConstructorMethodReflection(New_ $new): ?ReflectionMethod
    {
        $className = $this->getName($new->class);
        if ($className === null) {
            return null;
        }

        if (! $this->reflectionProvider->hasClass($className)) {
            return null;
        }

        $classReflection = $this->reflectionProvider->getClass($className);
        $reflectionClass = $classReflection->getNativeReflection();
        return $reflectionClass->getConstructor();
    }

    private function resolveClassToInstantiateByParameterReflection(ReflectionParameter $reflectionParameter): ?string
    {
        $reflectionType = $reflectionParameter->getType();
        if (! $reflectionType instanceof ReflectionType) {
            return null;
        }

        $requiredType = (string) $reflectionType;

        return $this->classToInstantiateByType[$requiredType] ?? null;
    }
}
