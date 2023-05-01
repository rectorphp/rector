<?php

declare (strict_types=1);
namespace Rector\Core\DependencyInjection\Skipper;

use Rector\Core\DependencyInjection\TypeResolver\ParameterTypeResolver;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use RectorPrefix202305\Symfony\Component\Config\Loader\LoaderInterface;
use RectorPrefix202305\Symfony\Component\DependencyInjection\Definition;
final class ParameterSkipper
{
    /**
     * Classes that create circular dependencies
     *
     * @var class-string<LoaderInterface>[]|string[]
     */
    private const DEFAULT_EXCLUDED_FATAL_CLASSES = ['Symfony\\Component\\Form\\FormExtensionInterface', 'Symfony\\Component\\Asset\\PackageInterface', 'Symfony\\Component\\Config\\Loader\\LoaderInterface', 'Symfony\\Component\\VarDumper\\Dumper\\ContextProvider\\ContextProviderInterface', 'EasyCorp\\Bundle\\EasyAdminBundle\\Form\\Type\\Configurator\\TypeConfiguratorInterface', 'Sonata\\CoreBundle\\Model\\Adapter\\AdapterInterface', 'Sonata\\Doctrine\\Adapter\\AdapterChain', 'Sonata\\Twig\\Extension\\TemplateExtension'];
    /**
     * @var string[]
     */
    private $excludedFatalClasses = [];
    /**
     * @readonly
     * @var \Rector\Core\DependencyInjection\TypeResolver\ParameterTypeResolver
     */
    private $parameterTypeResolver;
    /**
     * @param string[] $excludedFatalClasses
     */
    public function __construct(ParameterTypeResolver $parameterTypeResolver, array $excludedFatalClasses = [])
    {
        $this->parameterTypeResolver = $parameterTypeResolver;
        $this->excludedFatalClasses = \array_merge(self::DEFAULT_EXCLUDED_FATAL_CLASSES, $excludedFatalClasses);
    }
    public function shouldSkipParameter(ReflectionMethod $reflectionMethod, Definition $definition, ReflectionParameter $reflectionParameter) : bool
    {
        if (!$this->isArrayType($reflectionParameter)) {
            return \true;
        }
        // already set
        $argumentName = '$' . $reflectionParameter->getName();
        if (isset($definition->getArguments()[$argumentName])) {
            return \true;
        }
        $parameterType = $this->parameterTypeResolver->resolveParameterType($reflectionParameter->getName(), $reflectionMethod);
        if ($parameterType === null) {
            return \true;
        }
        if (\in_array($parameterType, $this->excludedFatalClasses, \true)) {
            return \true;
        }
        if (!\class_exists($parameterType) && !\interface_exists($parameterType)) {
            return \true;
        }
        // prevent circular dependency
        if ($definition->getClass() === null) {
            return \false;
        }
        return \is_a($definition->getClass(), $parameterType, \true);
    }
    private function isArrayType(ReflectionParameter $reflectionParameter) : bool
    {
        if (!$reflectionParameter->getType() instanceof ReflectionType) {
            return \false;
        }
        $reflectionParameterType = $reflectionParameter->getType();
        if (!$reflectionParameterType instanceof ReflectionNamedType) {
            return \false;
        }
        return $reflectionParameterType->getName() === 'array';
    }
}
