<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Sensio\Helper;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Exception\ShouldNotHappenException;
use Rector\Node\Attribute;

final class TemplateGuesser
{
    public function resolveFromClassMethodNode(ClassMethod $classMethodNode, int $version = 5): string
    {
        $namespace = (string) $classMethodNode->getAttribute(Attribute::NAMESPACE_NAME);
        $class = (string) $classMethodNode->getAttribute(Attribute::CLASS_NAME);
        $method = (string) $classMethodNode->name;

        if ($version === 3) {
            return $this->resolveForVersion3($namespace, $class, $method);
        }

        if ($version === 5) {
            return $this->resolveForVersion5($namespace, $class, $method);
        }

        throw new ShouldNotHappenException(sprintf(
            'Version "%d" is not supported in "%s". Add it.',
            $version,
            self::class
        ));
    }

    /**
     * Mimics https://github.com/sensiolabs/SensioFrameworkExtraBundle/blob/v3.0.0/Templating/TemplateGuesser.php
     */
    private function resolveForVersion3(string $namespace, string $class, string $method): string
    {
        // AppBundle\SomeNamespace\ => AppBundle
        // App\OtherBundle\SomeNamespace\ => OtherBundle
        $bundle = Strings::match($namespace, '/(?<bundle>[A-Za-z]*Bundle)/')['bundle'] ?? '';

        // SomeSuper\ControllerClass => ControllerClass
        $controller = Strings::match($class, '/(?<controller>[A-Za-z0-9]*)Controller$/')['controller'] ?? '';

        // indexAction => index
        $action = Strings::match($method, '/(?<method>[A-Za-z]*)Action$/')['method'] ?? '';

        return sprintf('%s:%s:%s.html.twig', $bundle, $controller, $action);
    }

    /**
     * Mimics https://github.com/sensiolabs/SensioFrameworkExtraBundle/blob/v5.0.0/Templating/TemplateGuesser.php
     */
    private function resolveForVersion5(string $namespace, string $class, string $method): string
    {
        $bundle = Strings::match($namespace, '/(?<bundle>[A-Za-z]*Bundle)/')['bundle'] ?? '';
        $bundle = preg_replace('/Bundle$/', '', $bundle);
        $bundle = $bundle ? '@' . $bundle . '/' : '';

        $controller = $this->resolveControllerVersion5($class);
        $action = preg_replace('/Action$/', '', $method);

        return sprintf('%s%s%s.html.twig', $bundle, $controller, $action);
    }

    private function resolveControllerVersion5(string $class): string
    {
        if (! preg_match('/Controller\\\(.+)Controller$/', $class, $tempMatch)) {
            return '';
        }

        $controller = str_replace('\\', '/', strtolower(
            preg_replace('/([a-z\d])([A-Z])/', '\\1_\\2', $tempMatch[1])
        ));

        return $controller ? $controller . '/' : '';
    }
}
