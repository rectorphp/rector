<?php

declare(strict_types=1);

namespace Rector\Transform\Rector\MethodCall;

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
use Rector\Transform\ValueObject\MethodCallToService;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Transform\Tests\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\ServiceGetterToConstructorInjectionRectorTest
 */
final class ServiceGetterToConstructorInjectionRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const METHOD_CALL_TO_SERVICES = 'method_call_to_services';

    /**
     * @var MethodCallToService[]
     */
    private $methodCallToServices = [];

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
                    self::METHOD_CALL_TO_SERVICES => [
                        new MethodCallToService('FirstService', 'getAnotherService', 'AnotherService'),
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

        foreach ($this->methodCallToServices as $methodCallToService) {
            if (! $this->isObjectType($node->var, $methodCallToService->getOldType())) {
                continue;
            }

            if (! $this->isName($node->name, $methodCallToService->getOldMethod())) {
                continue;
            }

            $serviceObjectType = new ObjectType($methodCallToService->getServiceType());

            $propertyName = $this->propertyNaming->fqnToVariableName($serviceObjectType);

            /** @var Class_ $classLike */
            $this->addConstructorDependencyToClass($classLike, $serviceObjectType, $propertyName);

            return new PropertyFetch(new Variable('this'), new Identifier($propertyName));
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $methodCallToServices = $configuration[self::METHOD_CALL_TO_SERVICES] ?? [];
        Assert::allIsInstanceOf($methodCallToServices, MethodCallToService::class);
        $this->methodCallToServices = $methodCallToServices;
    }
}
