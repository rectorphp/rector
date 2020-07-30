<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Generic\Tests\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\ServiceGetterToConstructorInjectionRectorTest
 * @see \Rector\Generic\Tests\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\ServiceGetterToConstructorInjectionRectorTest
 */
final class ServiceGetterToConstructorInjectionRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const METHOD_NAMES_BY_TYPES_TO_SERVICE_TYPES = '$methodNamesByTypesToServiceTypes';

    /**
     * @var mixed[]
     */
    private $methodNamesByTypesToServiceTypes = [];

    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    public function __construct(PropertyNaming $propertyNaming)
    {
        $this->propertyNaming = $propertyNaming;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Get service call to constructor injection', [
            new ConfiguredCodeSample(
                <<<'PHP'
final class SomeClass
{
    /**
     * @var FirstService
     */
    private $firstService;

    public function __construct(FirstService $firstService)
    {
        $this->firstService = $firstService;
    }

    public function run()
    {
        $anotherService = $this->firstService->getAnotherService();
        $anotherService->run();
    }
}

class FirstService
{
    /**
     * @var AnotherService
     */
    private $anotherService;

    public function __construct(AnotherService $anotherService)
    {
        $this->anotherService = $anotherService;
    }

    public function getAnotherService(): AnotherService
    {
         return $this->anotherService;
    }
}
PHP
                ,
                <<<'PHP'
final class SomeClass
{
    /**
     * @var FirstService
     */
    private $firstService;

    /**
     * @var AnotherService
     */
    private $anotherService;

    public function __construct(FirstService $firstService, AnotherService $anotherService)
    {
        $this->firstService = $firstService;
        $this->anotherService = $anotherService;
    }

    public function run()
    {
        $anotherService = $this->anotherService;
        $anotherService->run();
    }
}
PHP

                ,
                [
                    '$methodNamesByTypesToServiceTypes' => [
                        'FirstService' => [
                            'getAnotherService' => 'AnotherService',
                        ],
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
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $this->isNonAnonymousClass($classLike)) {
            return null;
        }

        foreach ($this->methodNamesByTypesToServiceTypes as $type => $methodNamesToServiceTypes) {
            if (! $this->isObjectType($node->var, $type)) {
                continue;
            }

            foreach ($methodNamesToServiceTypes as $methodName => $serviceType) {
                if (! $this->isName($node->name, $methodName)) {
                    continue;
                }

                $serviceObjectType = new ObjectType($serviceType);

                $propertyName = $this->propertyNaming->fqnToVariableName($serviceObjectType);

                /** @var Class_ $classLike */
                $this->addPropertyToClass($classLike, $serviceObjectType, $propertyName);

                return new PropertyFetch(new Variable('this'), new Identifier($propertyName));
            }
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $this->methodNamesByTypesToServiceTypes = $configuration[self::METHOD_NAMES_BY_TYPES_TO_SERVICE_TYPES] ?? [];
    }
}
