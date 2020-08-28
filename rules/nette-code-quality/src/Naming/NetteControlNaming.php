<?php

declare(strict_types=1);

namespace Rector\NetteCodeQuality\Naming;

use Nette\Utils\Strings;
use Rector\Core\Util\StaticRectorStrings;

final class NetteControlNaming
{
    public function createVariableName(string $shortName): string
    {
        $variableName = StaticRectorStrings::underscoreToCamelCase($shortName);
        if (Strings::endsWith($variableName, 'Form')) {
            return $variableName;
        }

        return $variableName . 'Control';
    }

    public function createCreateComponentClassMethodName(string $shortName): string
    {
        return 'createComponent' . StaticRectorStrings::underscoreToPascalCase($shortName);
    }
}
