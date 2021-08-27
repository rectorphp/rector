<?php

declare (strict_types=1);
namespace Rector\Nette\Naming;

use RectorPrefix20210827\Nette\Utils\Strings;
use RectorPrefix20210827\Stringy\Stringy;
final class NetteControlNaming
{
    public function createVariableName(string $shortName) : string
    {
        $stringy = new \RectorPrefix20210827\Stringy\Stringy($shortName);
        $variableName = (string) $stringy->camelize();
        if (\RectorPrefix20210827\Nette\Utils\Strings::endsWith($variableName, 'Form')) {
            return $variableName;
        }
        return $variableName . 'Control';
    }
    public function createCreateComponentClassMethodName(string $shortName) : string
    {
        $stringy = new \RectorPrefix20210827\Stringy\Stringy($shortName);
        $componentName = (string) $stringy->upperCamelize();
        return 'createComponent' . $componentName;
    }
}
