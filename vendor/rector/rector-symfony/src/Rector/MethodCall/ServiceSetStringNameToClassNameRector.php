<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Rector\MethodCall\ServiceSetStringNameToClassNameRector\ServiceSetStringNameToClassNameRectorTest
 */
final class ServiceSetStringNameToClassNameRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change $service->set() string names to class-type-based names, to allow $container->get() by types in Symfony 2.8', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node->name, 'set')) {
            return null;
        }
        if (\count($node->getArgs()) !== 2) {
            return null;
        }
        if (!$this->isObjectType($node->var, new ObjectType('Symfony\\Component\\DependencyInjection\\Loader\\Configurator\\ServicesConfigurator'))) {
            return null;
        }
        $args = $node->getArgs();
        $firstArg = $args[0];
        $serviceName = $this->valueResolver->getValue($firstArg->value);
        if (!\is_string($serviceName)) {
            return null;
        }
        // already slash renamed
        if (\strpos($serviceName, '\\') !== \false) {
            return null;
        }
        $secondArg = $args[1];
        if (!$secondArg->value instanceof ClassConstFetch && !$secondArg->value instanceof String_) {
            return null;
        }
        $serviceType = $this->valueResolver->getValue($secondArg->value);
        if (!\is_string($serviceType)) {
            return null;
        }
        $typedServiceName = \strtolower($serviceType);
        $firstArg->value = String_::fromString("'" . $typedServiceName . "'");
        return $node;
    }
}
