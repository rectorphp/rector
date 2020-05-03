<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony;

use Nette\Utils\Strings;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Util\StaticRectorStrings;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RouteResolver
{
    public function resolveFromParamsAndFileInfo(array $parameters, SmartFileInfo $smartFileInfo): string
    {
        $routeName = '';

        if (isset($parameters['controller'])) {
            $routeName = StaticRectorStrings::camelCaseToUnderscore($parameters['controller']);
        }

        if (isset($parameters['action'])) {
            $actionUnderscored = StaticRectorStrings::camelCaseToUnderscore($parameters['action']);

            if ($routeName !== '') {
                return $routeName . '_' . $actionUnderscored;
            }

            $shortDirectoryName = Strings::after($smartFileInfo->getPath(), '/', -1);
            if (! is_string($shortDirectoryName)) {
                throw new ShouldNotHappenException();
            }

            $shortDirectoryNameUnderscored = StaticRectorStrings::camelCaseToUnderscore($shortDirectoryName);

            return $shortDirectoryNameUnderscored . '_' . $actionUnderscored;
        }

        return $routeName;
    }
}
