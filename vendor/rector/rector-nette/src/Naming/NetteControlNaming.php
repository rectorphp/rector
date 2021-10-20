<?php

declare (strict_types=1);
namespace Rector\Nette\Naming;

use RectorPrefix20211020\Stringy\Stringy;
final class NetteControlNaming
{
    public function createVariableName(string $shortName) : string
    {
        $stringy = new \RectorPrefix20211020\Stringy\Stringy($shortName);
        $variableName = (string) $stringy->camelize();
        if (\substr_compare($variableName, 'Form', -\strlen('Form')) === 0) {
            return $variableName;
        }
        return $variableName . 'Control';
    }
    public function createCreateComponentClassMethodName(string $shortName) : string
    {
        $stringy = new \RectorPrefix20211020\Stringy\Stringy($shortName);
        $componentName = (string) $stringy->upperCamelize();
        return 'createComponent' . $componentName;
    }
}
