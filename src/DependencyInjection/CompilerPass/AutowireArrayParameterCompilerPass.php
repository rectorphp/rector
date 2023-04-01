<?php

declare (strict_types=1);
namespace Rector\Core\DependencyInjection\CompilerPass;

use RectorPrefix202304\Nette\Utils\Strings;
use Rector\Core\DependencyInjection\DefinitionFinder;
use Rector\Core\DependencyInjection\DocBlock\ParamTypeDocBlockResolver;
use Rector\Core\DependencyInjection\Skipper\ParameterSkipper;
use Rector\Core\DependencyInjection\TypeResolver\ParameterTypeResolver;
use ReflectionClass;
use ReflectionMethod;
use RectorPrefix202304\Symfony\Component\Config\Loader\LoaderInterface;
use RectorPrefix202304\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use RectorPrefix202304\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix202304\Symfony\Component\DependencyInjection\Definition;
use RectorPrefix202304\Symfony\Component\DependencyInjection\Reference;
/**
 * @inspiration https://github.com/nette/di/pull/178
 * @see \Rector\Core\Tests\DependencyInjection\CompilerPass\AutowireArrayParameterCompilerPassTest
 */
final class AutowireArrayParameterCompilerPass implements CompilerPassInterface
{
    /**
     * These namespaces are already configured by their bundles/extensions.
     *
     * @var string[]
     */
    private const EXCLUDED_NAMESPACES = ['Doctrine', 'JMS', 'Symfony', 'Sensio', 'Knp', 'EasyCorp', 'Sonata', 'Twig'];
    /**
     * Classes that create circular dependencies
     *
     * @var class-string<LoaderInterface>[]|string[]
     */
    private const EXCLUDED_FATAL_CLASSES = ['Symfony\\Component\\Form\\FormExtensionInterface', 'Symfony\\Component\\Asset\\PackageInterface', 'Symfony\\Component\\Config\\Loader\\LoaderInterface', 'Symfony\\Component\\VarDumper\\Dumper\\ContextProvider\\ContextProviderInterface', 'EasyCorp\\Bundle\\EasyAdminBundle\\Form\\Type\\Configurator\\TypeConfiguratorInterface', 'Sonata\\CoreBundle\\Model\\Adapter\\AdapterInterface', 'Sonata\\Doctrine\\Adapter\\AdapterChain', 'Sonata\\Twig\\Extension\\TemplateExtension', 'Symfony\\Component\\HttpKernel\\KernelInterface'];
    /**
     * @readonly
     * @var \Rector\Core\DependencyInjection\DefinitionFinder
     */
    private $definitionFinder;
    /**
     * @readonly
     * @var \Rector\Core\DependencyInjection\TypeResolver\ParameterTypeResolver
     */
    private $parameterTypeResolver;
    /**
     * @readonly
     * @var \Rector\Core\DependencyInjection\Skipper\ParameterSkipper
     */
    private $parameterSkipper;
    /**
     * @param string[] $excludedFatalClasses
     */
    public function __construct(array $excludedFatalClasses = [])
    {
        $this->definitionFinder = new DefinitionFinder();
        $paramTypeDocBlockResolver = new ParamTypeDocBlockResolver();
        $this->parameterTypeResolver = new ParameterTypeResolver($paramTypeDocBlockResolver);
        $this->parameterSkipper = new ParameterSkipper($this->parameterTypeResolver, $excludedFatalClasses);
    }
    public function process(ContainerBuilder $containerBuilder) : void
    {
        $definitions = $containerBuilder->getDefinitions();
        foreach ($definitions as $definition) {
            if ($this->shouldSkipDefinition($containerBuilder, $definition)) {
                continue;
            }
            /** @var ReflectionClass<object> $reflectionClass */
            $reflectionClass = $containerBuilder->getReflectionClass($definition->getClass());
            /** @var ReflectionMethod $constructorReflectionMethod */
            $constructorReflectionMethod = $reflectionClass->getConstructor();
            $this->processParameters($containerBuilder, $constructorReflectionMethod, $definition);
        }
    }
    private function shouldSkipDefinition(ContainerBuilder $containerBuilder, Definition $definition) : bool
    {
        if ($definition->isAbstract()) {
            return \true;
        }
        if ($definition->getClass() === null) {
            return \true;
        }
        // here class name can be "%parameter.class%"
        $parameterBag = $containerBuilder->getParameterBag();
        $resolvedClassName = $parameterBag->resolveValue($definition->getClass());
        // skip 3rd party classes, they're autowired by own config
        $excludedNamespacePattern = '#^(' . \implode('|', self::EXCLUDED_NAMESPACES) . ')\\\\#';
        if (Strings::match($resolvedClassName, $excludedNamespacePattern)) {
            return \true;
        }
        if (\in_array($resolvedClassName, self::EXCLUDED_FATAL_CLASSES, \true)) {
            return \true;
        }
        if ($definition->getFactory()) {
            return \true;
        }
        if (!\class_exists($definition->getClass())) {
            return \true;
        }
        $reflectionClass = $containerBuilder->getReflectionClass($definition->getClass());
        if (!$reflectionClass instanceof ReflectionClass) {
            return \true;
        }
        if (!$reflectionClass->hasMethod('__construct')) {
            return \true;
        }
        /** @var ReflectionMethod $constructorReflectionMethod */
        $constructorReflectionMethod = $reflectionClass->getConstructor();
        return !$constructorReflectionMethod->getParameters();
    }
    private function processParameters(ContainerBuilder $containerBuilder, ReflectionMethod $reflectionMethod, Definition $definition) : void
    {
        $reflectionParameters = $reflectionMethod->getParameters();
        foreach ($reflectionParameters as $reflectionParameter) {
            if ($this->parameterSkipper->shouldSkipParameter($reflectionMethod, $definition, $reflectionParameter)) {
                continue;
            }
            $parameterType = $this->parameterTypeResolver->resolveParameterType($reflectionParameter->getName(), $reflectionMethod);
            if ($parameterType === null) {
                continue;
            }
            $definitionsOfType = $this->definitionFinder->findAllByType($containerBuilder, $parameterType);
            $definitionsOfType = $this->filterOutAbstractDefinitions($definitionsOfType);
            $argumentName = '$' . $reflectionParameter->getName();
            $definition->setArgument($argumentName, $this->createReferencesFromDefinitions($definitionsOfType));
        }
    }
    /**
     * Abstract definitions cannot be the target of references
     *
     * @param Definition[] $definitions
     * @return Definition[]
     */
    private function filterOutAbstractDefinitions(array $definitions) : array
    {
        foreach ($definitions as $key => $definition) {
            if ($definition->isAbstract()) {
                unset($definitions[$key]);
            }
        }
        return $definitions;
    }
    /**
     * @param Definition[] $definitions
     * @return Reference[]
     */
    private function createReferencesFromDefinitions(array $definitions) : array
    {
        $references = [];
        $definitionOfTypeNames = \array_keys($definitions);
        foreach ($definitionOfTypeNames as $definitionOfTypeName) {
            $references[] = new Reference($definitionOfTypeName);
        }
        return $references;
    }
}
