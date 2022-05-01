<?php

declare (strict_types=1);
namespace Rector\Symfony\Helper;

use RectorPrefix20220501\Nette\Utils\Strings;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Symfony\BundleClassResolver;
/**
 * @see \Rector\Symfony\Tests\Rector\ClassMethod\TemplateAnnotationToThisRenderRector\TemplateAnnotationToThisRenderRectorTest
 */
final class TemplateGuesser
{
    /**
     * @var string
     * @see https://regex101.com/r/yZAUAC/1
     */
    private const BUNDLE_SUFFIX_REGEX = '#Bundle$#';
    /**
     * @var string
     * @see https://regex101.com/r/T6ItFG/1
     */
    private const BUNDLE_NAME_MATCHING_REGEX = '#(?<bundle>[\\w]*Bundle)#';
    /**
     * @var string
     * @see https://regex101.com/r/5dNkCC/2
     */
    private const SMALL_LETTER_BIG_LETTER_REGEX = '#([a-z\\d])([A-Z])#';
    /**
     * @var string
     * @see https://regex101.com/r/YUrmAD/1
     */
    private const CONTROLLER_NAME_MATCH_REGEX = '#Controller\\\\(?<class_name_without_suffix>.+)Controller$#';
    /**
     * @var string
     * @see https://regex101.com/r/nj8Ojf/1
     */
    private const ACTION_MATCH_REGEX = '#Action$#';
    /**
     * @readonly
     * @var \Rector\Symfony\BundleClassResolver
     */
    private $bundleClassResolver;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\Symfony\BundleClassResolver $bundleClassResolver, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->bundleClassResolver = $bundleClassResolver;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function resolveFromClassMethodNode(\PhpParser\Node\Stmt\ClassMethod $classMethod) : string
    {
        $scope = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $namespace = $scope->getNamespace();
        if (!\is_string($namespace)) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $className = ($getClassReflection = $scope->getClassReflection()) ? $getClassReflection->getName() : null;
        if (!\is_string($className)) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        /** @var string $methodName */
        $methodName = $this->nodeNameResolver->getName($classMethod);
        return $this->resolve($namespace, $className, $methodName);
    }
    /**
     * Mimics https://github.com/sensiolabs/SensioFrameworkExtraBundle/blob/v5.0.0/Templating/TemplateGuesser.php
     */
    private function resolve(string $namespace, string $class, string $method) : string
    {
        $bundle = $this->resolveBundle($class, $namespace);
        $controller = $this->resolveController($class);
        $action = \RectorPrefix20220501\Nette\Utils\Strings::replace($method, self::ACTION_MATCH_REGEX, '');
        $fullPath = '';
        if ($bundle !== '') {
            $fullPath .= $bundle . '/';
        }
        if ($controller !== '') {
            $fullPath .= $controller . '/';
        }
        return $fullPath . $action . '.html.twig';
    }
    private function resolveBundle(string $class, string $namespace) : string
    {
        $shortBundleClass = $this->bundleClassResolver->resolveShortBundleClassFromControllerClass($class);
        if ($shortBundleClass !== null) {
            return '@' . $shortBundleClass;
        }
        $bundle = \RectorPrefix20220501\Nette\Utils\Strings::match($namespace, self::BUNDLE_NAME_MATCHING_REGEX)['bundle'] ?? '';
        $bundle = \RectorPrefix20220501\Nette\Utils\Strings::replace($bundle, self::BUNDLE_SUFFIX_REGEX, '');
        return $bundle !== '' ? '@' . $bundle : '';
    }
    private function resolveController(string $class) : string
    {
        $match = \RectorPrefix20220501\Nette\Utils\Strings::match($class, self::CONTROLLER_NAME_MATCH_REGEX);
        if ($match === null) {
            return '';
        }
        $controller = \RectorPrefix20220501\Nette\Utils\Strings::replace($match['class_name_without_suffix'], self::SMALL_LETTER_BIG_LETTER_REGEX, '1_\\2');
        return \str_replace('\\', '/', $controller);
    }
}
