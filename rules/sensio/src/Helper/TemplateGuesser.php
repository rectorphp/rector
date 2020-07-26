<?php

declare(strict_types=1);

namespace Rector\Sensio\Helper;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Sensio\BundleClassResolver;

/**
 * @see \Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationToThisRenderRector\TemplateAnnotationToThisRenderRectorTest
 */
final class TemplateGuesser
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var BundleClassResolver
     */
    private $bundleClassResolver;

    public function __construct(BundleClassResolver $bundleClassResolver, NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->bundleClassResolver = $bundleClassResolver;
    }

    public function resolveFromClassMethodNode(ClassMethod $classMethod): string
    {
        $namespace = $classMethod->getAttribute(AttributeKey::NAMESPACE_NAME);
        if (! is_string($namespace)) {
            throw new ShouldNotHappenException();
        }

        $class = $classMethod->getAttribute(AttributeKey::CLASS_NAME);
        if (! is_string($class)) {
            throw new ShouldNotHappenException();
        }

        $method = $this->nodeNameResolver->getName($classMethod);
        if ($method === null) {
            throw new ShouldNotHappenException();
        }

        return $this->resolve($namespace, $class, $method);
    }

    /**
     * Mimics https://github.com/sensiolabs/SensioFrameworkExtraBundle/blob/v5.0.0/Templating/TemplateGuesser.php
     */
    private function resolve(string $namespace, string $class, string $method): string
    {
        $bundle = $this->resolveBundle($class, $namespace);
        $controller = $this->resolveController($class);

        $action = Strings::replace($method, '#Action$#');

        $fullPath = '';
        if ($bundle !== '') {
            $fullPath .= $bundle . '/';
        }

        if ($controller !== '') {
            $fullPath .= $controller . '/';
        }

        return $fullPath . $action . '.html.twig';
    }

    private function resolveController(string $class): string
    {
        $match = Strings::match($class, '#Controller\\\(.+)Controller$#');
        if (! $match) {
            return '';
        }

        $controller = Strings::replace($match[1], '#([a-z\d])([A-Z])#', '\\1_\\2');
        return str_replace('\\', '/', $controller);
    }

    private function resolveBundle(string $class, string $namespace): string
    {
        $shortBundleClass = $this->bundleClassResolver->resolveShortBundleClassFromControllerClass($class);
        if ($shortBundleClass !== null) {
            return '@' . $shortBundleClass;
        }

        $bundle = Strings::match($namespace, '#(?<bundle>[\w]*Bundle)#')['bundle'] ?? '';
        $bundle = Strings::replace($bundle, '#Bundle$#');
        return $bundle !== '' ? '@' . $bundle : '';
    }
}
