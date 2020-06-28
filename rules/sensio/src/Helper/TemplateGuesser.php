<?php

declare(strict_types=1);

namespace Rector\Sensio\Helper;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector\TemplateAnnotationRectorTest
 */
final class TemplateGuesser
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
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
        $bundle = Strings::match($namespace, '#(?<bundle>[\w]*Bundle)#')['bundle'] ?? '';
        $bundle = Strings::replace($bundle, '#Bundle$#');
        $bundle = $bundle !== '' ? '@' . $bundle . '/' : '';

        $controller = $this->resolveControllerVersion5($class);
        $action = Strings::replace($method, '#Action$#');

        return sprintf('%s%s%s.html.twig', $bundle, $controller, $action);
    }

    private function resolveControllerVersion5(string $class): string
    {
        $match = Strings::match($class, '#Controller\\\(.+)Controller$#');
        if (! $match) {
            return '';
        }

        $controller = Strings::replace($match[1], '#([a-z\d])([A-Z])#', '\\1_\\2');
        $controller = strtolower($controller);
        $controller = str_replace('\\', '/', $controller);

        return $controller !== '' ? $controller . '/' : '';
    }
}
