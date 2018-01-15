<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer\Contrib\Symfony;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Node\Attribute;

/**
 * Mimics https://github.com/sensiolabs/SensioFrameworkExtraBundle/blob/072c00c52b947e88a1e619e9ff426cee6c8c482b/Templating/TemplateGuesser.php
 * only without Symfony dependency
 */
final class TemplateGuesser
{
    public function resolveFromClassMethodNode(ClassMethod $classMethodNode): string
    {
        $namespace = (string) $classMethodNode->getAttribute(Attribute::NAMESPACE_NAME);
        $class = (string) $classMethodNode->getAttribute(Attribute::CLASS_NAME);
        $method = $classMethodNode->name->toString();

        // AppBundle\SomeNamespace\ => AppBundle
        // App\OtherBundle\SomeNamespace\ => OtherBundle
        $templateName = Strings::match($namespace, '/(?<bundle>[A-Za-z]*Bundle)/')['bundle'] ?? '';
        $templateName .= ':';

        // SomeSuper\ControllerClass => ControllerClass
        $templateName .= Strings::match($class, '/(?<controller>[A-Za-z0-9]*)Controller$/')['controller'] ?? '';
        $templateName .= ':';

        // indexAction => index
        $templateName .= Strings::match($method, '/(?<method>[A-Za-z]*)Action$/')['method'] ?? '';

        // suffix
        $templateName .= '.html.twig';

        return $templateName;
    }
}
