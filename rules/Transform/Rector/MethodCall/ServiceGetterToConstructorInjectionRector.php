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
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Collector\PropertyToAddCollector;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Rector\Transform\ValueObject\ServiceGetterToConstructorInjection;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Tests\Transform\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\ServiceGetterToConstructorInjectionRectorTest
 */
final class ServiceGetterToConstructorInjectionRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const METHOD_CALL_TO_SERVICES = 'method_call_to_services';

    /**
     * @var ServiceGetterToConstructorInjection[]
     */
    private array $methodCallToServices = [];

    public function __construct(
        private PropertyNaming $propertyNaming,
        private ClassAnalyzer $classAnalyzer,
        private PropertyToAddCollector $propertyToAddCollector
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Get service call to constructor injection', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
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
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
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
CODE_SAMPLE
                ,
                [
                    self::METHOD_CALL_TO_SERVICES => [
                        new ServiceGetterToConstructorInjection('FirstService', 'getAnotherService', 'AnotherService'),
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
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return null;
        }

        if ($this->classAnalyzer->isAnonymousClass($classLike)) {
            return null;
        }

        foreach ($this->methodCallToServices as $methodCallToService) {
            if (! $this->isObjectType($node->var, $methodCallToService->getOldObjectType())) {
                continue;
            }

            if (! $this->isName($node->name, $methodCallToService->getOldMethod())) {
                continue;
            }

            $serviceObjectType = new ObjectType($methodCallToService->getServiceType());

            $propertyName = $this->propertyNaming->fqnToVariableName($serviceObjectType);

            $propertyMetadata = new PropertyMetadata($propertyName, $serviceObjectType, Class_::MODIFIER_PRIVATE);
            $this->propertyToAddCollector->addPropertyToClass($classLike, $propertyMetadata);

            return new PropertyFetch(new Variable('this'), new Identifier($propertyName));
        }

        return $node;
    }

    /**
     * @param array<string, ServiceGetterToConstructorInjection[]> $configuration
     */
    public function configure(array $configuration): void
    {
        $methodCallToServices = $configuration[self::METHOD_CALL_TO_SERVICES] ?? [];
        Assert::allIsInstanceOf($methodCallToServices, ServiceGetterToConstructorInjection::class);
        $this->methodCallToServices = $methodCallToServices;
    }
}
