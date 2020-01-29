<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveOverriddenValuesRector\Fixture;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Util\RectorStrings;

class KeepPresenterName
{
    public function resolvePathFromClassAndMethodNodes(Class_ $classNode, ClassMethod $classMethod): string
    {
        /** @var string $presenterName */
        $presenterName = $this->getName($classNode);

        /** @var string $presenterPart */
        $presenterPart = Strings::after($presenterName, '\\', -1);

        /** @var string $presenterPart */
        $presenterPart = Strings::substring($presenterPart, 0, -Strings::length('Presenter'));
        $presenterPart = RectorStrings::camelCaseToDashes($presenterPart);

        $match = (array) Strings::match($this->getName($classMethod), '#^(action|render)(?<short_action_name>.*?$)#sm');
        $actionPart = lcfirst($match['short_action_name']);

        return $presenterPart . '/' . $actionPart;
    }
}
