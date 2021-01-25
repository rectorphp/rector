<?php

declare(strict_types=1);

namespace Rector\Restoration\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\Restoration\Tests\Rector\New_\CompleteMissingDependencyInNewRector\CompleteMissingDependencyInNewRectorTest
 */
final class CompleteMissingDependencyInNewRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const CLASS_TO_INSTANTIATE_BY_TYPE = '$classToInstantiateByType';

    /**
     * @var string[]
     */
    private $classToInstantiateByType = [];

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
                , [
                    self::CLASS_TO_INSTANTIATE_BY_TYPE => [
                        'RandomDependency' => 'RandomDependency',
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
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

        if (! class_exists($className)) {
            return null;
        }

        $reflectionClass = new ReflectionClass($className);

        return $reflectionClass->getConstructor();
    }

    private function resolveClassToInstantiateByParameterReflection(ReflectionParameter $reflectionParameter): ?string
    {
        $parameterType = $reflectionParameter->getType();
        if (! $parameterType instanceof ReflectionType) {
            return null;
        }

        $requiredType = (string) $parameterType;

        return $this->classToInstantiateByType[$requiredType] ?? null;
    }
}
