<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer\Contrib\Symfony;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Node\Attribute;

/**
 * Mimics https://github.com/sensiolabs/SensioFrameworkExtraBundle/blob/v3.0.29/Templating/TemplateGuesser.php
 * only without Symfony dependency
 */
final class TemplateGuesser
{
    public function resolveFromClassMethodNode(ClassMethod $classMethodNode): string
    {
        $namespace = (string) $classMethodNode->getAttribute(Attribute::NAMESPACE_NAME);
        $class = (string) $classMethodNode->getAttribute(Attribute::CLASS_NAME);
        $method = $classMethodNode->name->toString();

        // converts AppBundle\SomeNamespace\ => AppBundle
        // converts App\OtherBundle\SomeNamespace\ => OtherBundle
        // This check may fail if people dont follow naming conventions
        $bundle = Strings::match($namespace, '/(?<bundle>[A-Za-z]*Bundle)/')['bundle'] ?? '';
        $controller = Strings::match($class, '/(?<controller>[A-Za-z0-9]*)Controller$/')['controller'] ?? '';
        $action = Strings::match($method, '/(?<method>[A-Za-z]*)Action$/')['method'] ?? '';

        return sprintf('%s:%s:%s.html.twig', $bundle, $controller, $action);
    }
}
