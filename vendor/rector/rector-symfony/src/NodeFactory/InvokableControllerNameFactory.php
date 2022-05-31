<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeFactory;

use RectorPrefix20220531\Nette\Utils\Strings;
use PhpParser\Node\Identifier;
final class InvokableControllerNameFactory
{
    public function createControllerName(\PhpParser\Node\Identifier $controllerIdentifier, string $actionMethodName) : string
    {
        $oldClassName = $controllerIdentifier->toString();
        if (\strncmp($actionMethodName, 'action', \strlen('action')) === 0) {
            $actionMethodName = \RectorPrefix20220531\Nette\Utils\Strings::substring($actionMethodName, \strlen('Action'));
        }
        if (\substr_compare($actionMethodName, 'Action', -\strlen('Action')) === 0) {
            $actionMethodName = \RectorPrefix20220531\Nette\Utils\Strings::substring($actionMethodName, 0, -\strlen('Action'));
        }
        $actionMethodName = \ucfirst($actionMethodName);
        return \RectorPrefix20220531\Nette\Utils\Strings::replace($oldClassName, '#(.*?)Controller#', '$1' . $actionMethodName . 'Controller');
    }
}
