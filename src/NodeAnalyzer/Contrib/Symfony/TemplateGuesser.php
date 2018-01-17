<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer\Contrib\Symfony;

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
        $method = $classMethodNode->name->toString();

        if ($version === 5) {
            $templateName = $this->resolveForVersion5($namespace, $class, $method);
        } else {
            throw new ShouldNotHappenException(sprintf(
                'Version %d is not supported in %s. Add it.',
                $version,
                self::class
            ));
        }

        return $templateName . '.html.twig';
    }

    /**
     * Mimics https://github.com/sensiolabs/SensioFrameworkExtraBundle/blob/v3.0.0/Templating/TemplateGuesser.php
     */
    private function resolveForVersion5(string $namespace, string $class, string $method): string
    {
        // AppBundle\SomeNamespace\ => AppBundle
        // App\OtherBundle\SomeNamespace\ => OtherBundle
        $templateName = Strings::match($namespace, '/(?<bundle>[A-Za-z]*Bundle)/')['bundle'] ?? '';
        $templateName .= ':';

        // SomeSuper\ControllerClass => ControllerClass
        $templateName .= Strings::match($class, '/(?<controller>[A-Za-z0-9]*)Controller$/')['controller'] ?? '';
        $templateName .= ':';

        // indexAction => index
        $templateName .= Strings::match($method, '/(?<method>[A-Za-z]*)Action$/')['method'] ?? '';

        return $templateName;
    }
}
