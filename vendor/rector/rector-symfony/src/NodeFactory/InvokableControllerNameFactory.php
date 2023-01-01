<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeFactory;

use RectorPrefix202301\Nette\Utils\Strings;
use PhpParser\Node\Identifier;
final class InvokableControllerNameFactory
{
    public function createControllerName(Identifier $controllerIdentifier, string $actionMethodName) : string
    {
        $oldClassName = $controllerIdentifier->toString();
        if (\strncmp($actionMethodName, 'action', \strlen('action')) === 0) {
            $actionMethodName = Strings::substring($actionMethodName, \strlen('Action'));
        }
        if (\substr_compare($actionMethodName, 'Action', -\strlen('Action')) === 0) {
            $actionMethodName = Strings::substring($actionMethodName, 0, -\strlen('Action'));
        }
        $actionMethodName = \ucfirst($actionMethodName);
        return Strings::replace($oldClassName, '#(.*?)Controller#', '$1' . $actionMethodName . 'Controller');
    }
}
