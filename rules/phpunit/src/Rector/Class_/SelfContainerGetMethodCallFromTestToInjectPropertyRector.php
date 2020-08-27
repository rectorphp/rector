<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\Core\Rector\AbstractPHPUnitRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPUnit\Collector\FormerVariablesByMethodCollector;
use Rector\PHPUnit\Manipulator\OnContainerGetCallManipulator;
use Rector\SymfonyPHPUnit\Node\KernelTestCaseNodeFactory;
use Rector\SymfonyPHPUnit\Rector\Class_\SelfContainerGetMethodCallFromTestToSetUpMethodRector;
use Rector\SymfonyPHPUnit\SelfContainerMethodCallCollector;

/**
 * Inspiration
 * @see SelfContainerGetMethodCallFromTestToSetUpMethodRector
 *
 * @see https://github.com/shopsys/shopsys/pull/1392
 * @see https://github.com/jakzal/phpunit-injector
 *
 * @see \Rector\PHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToInjectPropertyRector\SelfContainerGetMethodCallFromTestToInjectPropertyRectorTest
 */
final class SelfContainerGetMethodCallFromTestToInjectPropertyRector extends AbstractPHPUnitRector
{
    /**
     * @var SelfContainerMethodCallCollector
     */
    private $selfContainerMethodCallCollector;

    /**
     * @var KernelTestCaseNodeFactory
     */
    private $kernelTestCaseNodeFactory;

    /**
     * @var OnContainerGetCallManipulator
     */
    private $onContainerGetCallManipulator;

    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    /**
     * @var FormerVariablesByMethodCollector
     */
    private $formerVariablesByMethodCollector;

    public function __construct(
        ClassManipulator $classManipulator,
        KernelTestCaseNodeFactory $kernelTestCaseNodeFactory,
        OnContainerGetCallManipulator $onContainerGetCallManipulator,
        SelfContainerMethodCallCollector $selfContainerMethodCallCollector,
        FormerVariablesByMethodCollector $formerVariablesByMethodCollector
    ) {
        $this->selfContainerMethodCallCollector = $selfContainerMethodCallCollector;
        $this->kernelTestCaseNodeFactory = $kernelTestCaseNodeFactory;
        $this->onContainerGetCallManipulator = $onContainerGetCallManipulator;
        $this->classManipulator = $classManipulator;
        $this->formerVariablesByMethodCollector = $formerVariablesByMethodCollector;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Change $container->get() calls in PHPUnit to @inject properties autowired by jakzal/phpunit-injector',
            [
                new CodeSample(
                    <<<'PHP'
use PHPUnit\Framework\TestCase;
class SomeClassTest extends TestCase {
    public function test()
    {
        $someService = $this->getContainer()->get(SomeService::class);
    }
}

class SomeService
{
}
PHP
,
                    <<<'PHP'
use PHPUnit\Framework\TestCase;
class SomeClassTest extends TestCase {
    /**
     * @var SomeService
     * @inject
     */
    private $someService;
    public function test()
    {
        $someService = $this->someService;
    }
}

class SomeService
{
}
PHP
            ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isInTestClass($node)) {
            return null;
        }

        // 1. find self::$container->get(x)
        $serviceTypes = $this->selfContainerMethodCallCollector->collectContainerGetServiceTypes($node, false);
        if (count($serviceTypes) === 0) {
            return null;
        }

        // 2. - add @inject to existing properties of that type, to prevent re-adding them
        foreach ($serviceTypes as $key => $serviceType) {
            $existingProperty = $this->classManipulator->findPropertyByType($node, $serviceType);
            if ($existingProperty !== null) {
                $this->addInjectAnnotationToProperty($existingProperty);
                unset($serviceTypes[$key]);
            }
        }

        // 3. create private properties with this types
        $privateProperties = $this->kernelTestCaseNodeFactory->createPrivatePropertiesFromTypes($node, $serviceTypes);
        $this->addInjectAnnotationToProperties($privateProperties);
        $node->stmts = array_merge($privateProperties, $node->stmts);

        // 4. remove old in-method $property assigns
        $this->formerVariablesByMethodCollector->reset();

        $this->onContainerGetCallManipulator->removeAndCollectFormerAssignedVariables($node, false);

        // 4. replace former variables by $this->someProperty
        $this->onContainerGetCallManipulator->replaceFormerVariablesWithPropertyFetch($node);

        return $node;
    }

    private function addInjectAnnotationToProperty(Property $privateProperty): void
    {
        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $privateProperty->getAttribute(AttributeKey::PHP_DOC_INFO);
        $phpDocInfo->addBareTag('@inject');
    }

    /**
     * @param Property[] $properties
     */
    private function addInjectAnnotationToProperties(array $properties): void
    {
        foreach ($properties as $property) {
            $this->addInjectAnnotationToProperty($property);
        }
    }
}
