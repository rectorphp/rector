<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony;

use Nette\Utils\Strings;
use Rector\Exception\ShouldNotHappenException;
use Rector\Util\RectorStrings;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RouteResolver
{
    public function resolveFromParamsAndFileInfo(array $parameters, SmartFileInfo $smartFileInfo): string
    {
        $routeName = '';

        if (isset($parameters['controller'])) {
            $routeName = RectorStrings::camelCaseToUnderscore($parameters['controller']);
        }

        if (isset($parameters['action'])) {
            $actionUnderscored = RectorStrings::camelCaseToUnderscore($parameters['action']);

            if ($routeName !== '') {
                return $routeName . '_' . $actionUnderscored;
            }

            $shortDirectoryName = Strings::after($smartFileInfo->getPath(), '/', -1);
            if (! is_string($shortDirectoryName)) {
                throw new ShouldNotHappenException();
            }

            $shortDirectoryNameUnderscored = RectorStrings::camelCaseToUnderscore($shortDirectoryName);

            return $shortDirectoryNameUnderscored . '_' . $actionUnderscored;
        }

        return $routeName;
    }
}
