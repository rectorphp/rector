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
        $controllerPatterns[] = '/Controller\\\(.+)Controller$/';

        $namespace = (string) $classMethodNode->getAttribute(Attribute::NAMESPACE_NAME);
        $className = (string) $classMethodNode->getAttribute(Attribute::CLASS_NAME);
        $method = $classMethodNode->name->toString();

        // AppBundle\SomeNamespace\ => AppBundle
        // App\OtherBundle\SomeNamespace\ => OtherBundle
        $bundleName = Strings::match($namespace, '/(?<bundle>[A-Za-z]*Bundle)/')['bundle'] ?? '';
        $bundleName = preg_replace('/Bundle$/', '', $bundleName);

        $matchController = null;
        foreach ($controllerPatterns as $pattern) {
            if (preg_match($pattern, $className, $tempMatch)) {
                $matchController = str_replace('\\', '/', strtolower(preg_replace('/([a-z\d])([A-Z])/', '\\1_\\2', $tempMatch[1])));
                break;
            }
        }
        if (null === $matchController) {
            throw new \InvalidArgumentException(sprintf('The "%s" class does not look like a controller class (its FQN must match one of the following regexps: "%s")', $className, implode('", "', $controllerPatterns)));
        }

        $matchAction = preg_replace('/Action$/', '', $method);

        return sprintf(($bundleName ? '@'.$bundleName.'/' : '').$matchController.($matchController ? '/' : '').$matchAction.'.html.twig');
    }
}
