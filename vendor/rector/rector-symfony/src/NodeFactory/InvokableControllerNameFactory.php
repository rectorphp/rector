<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Symfony\NodeFactory;

use RectorPrefix20220606\Nette\Utils\Strings;
use RectorPrefix20220606\PhpParser\Node\Identifier;
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
