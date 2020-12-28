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
 * @see \Rector\Sensio\Tests\Rector\ClassMethod\TemplateAnnotationToThisRenderRector\TemplateAnnotationToThisRenderRectorTest
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
    private const BUNDLE_NAME_MATCHING_REGEX = '#(?<bundle>[\w]*Bundle)#';

    /**
     * @var string
     * @see https://regex101.com/r/5dNkCC/2
     */
    private const SMALL_LETTER_BIG_LETTER_REGEX = '#([a-z\d])([A-Z])#';

    /**
     * @var string
     * @see https://regex101.com/r/YUrmAD/1
     */
    private const CONTROLLER_NAME_MATCH_REGEX = '#Controller\\\(?<class_name_without_suffix>.+)Controller$#';

    /**
     * @var string
     * @see https://regex101.com/r/nj8Ojf/1
     */
    private const ACTION_MATCH_REGEX = '#Action$#';

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

        $methodName = $this->nodeNameResolver->getName($classMethod);
        return $this->resolve($namespace, $class, $methodName);
    }

    /**
     * Mimics https://github.com/sensiolabs/SensioFrameworkExtraBundle/blob/v5.0.0/Templating/TemplateGuesser.php
     */
    private function resolve(string $namespace, string $class, string $method): string
    {
        $bundle = $this->resolveBundle($class, $namespace);
        $controller = $this->resolveController($class);

        $action = Strings::replace($method, self::ACTION_MATCH_REGEX, '');

        $fullPath = '';
        if ($bundle !== '') {
            $fullPath .= $bundle . '/';
        }

        if ($controller !== '') {
            $fullPath .= $controller . '/';
        }

        return $fullPath . $action . '.html.twig';
    }

    private function resolveBundle(string $class, string $namespace): string
    {
        $shortBundleClass = $this->bundleClassResolver->resolveShortBundleClassFromControllerClass($class);
        if ($shortBundleClass !== null) {
            return '@' . $shortBundleClass;
        }

        $bundle = Strings::match($namespace, self::BUNDLE_NAME_MATCHING_REGEX)['bundle'] ?? '';
        $bundle = Strings::replace($bundle, self::BUNDLE_SUFFIX_REGEX, '');
        return $bundle !== '' ? '@' . $bundle : '';
    }

    private function resolveController(string $class): string
    {
        $match = Strings::match($class, self::CONTROLLER_NAME_MATCH_REGEX);
        if (! $match) {
            return '';
        }

        $controller = Strings::replace(
            $match['class_name_without_suffix'],
            self::SMALL_LETTER_BIG_LETTER_REGEX,
            '\\1_\\2'
        );
        return str_replace('\\', '/', $controller);
    }
}
