<?php

declare (strict_types=1);
namespace Rector\Symfony\Configs\Rector\Closure;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Symfony\DataProvider\ServiceNameToTypeUniqueProvider;
use Rector\Symfony\NodeAnalyzer\SymfonyPhpClosureDetector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Configs\Rector\Closure\ServiceSetStringNameToClassNameRector\ServiceSetStringNameToClassNameRectorTest
 */
final class ServiceSetStringNameToClassNameRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\SymfonyPhpClosureDetector
     */
    private $symfonyPhpClosureDetector;
    /**
     * @readonly
     * @var \Rector\Symfony\DataProvider\ServiceNameToTypeUniqueProvider
     */
    private $serviceNameToTypeUniqueProvider;
    public function __construct(SymfonyPhpClosureDetector $symfonyPhpClosureDetector, ServiceNameToTypeUniqueProvider $serviceNameToTypeUniqueProvider)
    {
        $this->symfonyPhpClosureDetector = $symfonyPhpClosureDetector;
        $this->serviceNameToTypeUniqueProvider = $serviceNameToTypeUniqueProvider;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change $service->set() string names to class-type-based names, to allow $container->get() by types in Symfony 2.8. Provide XML config via $rectorConfig->symfonyContainerXml(...);', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set('some_name', App\SomeClass::class);
};
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set('app\\someclass', App\SomeClass::class);
};
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
        if (!$this->symfonyPhpClosureDetector->detect($node)) {
            return null;
        }
        $serviceNamesToType = $this->serviceNameToTypeUniqueProvider->provide();
        $hasChanged = \false;
        $this->traverseNodesWithCallable($node->stmts, function (Node $node) use($serviceNamesToType, &$hasChanged) {
            if (!$node instanceof String_) {
                return null;
            }
            foreach ($serviceNamesToType as $serviceName => $serviceType) {
                if ($node->value !== $serviceName) {
                    continue;
                }
                $hasChanged = \true;
                return $this->createTypedServiceName($serviceType);
            }
            return null;
        });
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function createTypedServiceName(string $serviceType) : String_
    {
        $typedServiceName = \strtolower($serviceType);
        return String_::fromString("'" . $typedServiceName . "'");
    }
}
