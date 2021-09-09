<?php

declare (strict_types=1);
namespace RectorPrefix20210909\Doctrine\Inflector\Rules\Portuguese;

use RectorPrefix20210909\Doctrine\Inflector\GenericLanguageInflectorFactory;
use RectorPrefix20210909\Doctrine\Inflector\Rules\Ruleset;
final class InflectorFactory extends \RectorPrefix20210909\Doctrine\Inflector\GenericLanguageInflectorFactory
{
    protected function getSingularRuleset() : \RectorPrefix20210909\Doctrine\Inflector\Rules\Ruleset
    {
        return \RectorPrefix20210909\Doctrine\Inflector\Rules\Portuguese\Rules::getSingularRuleset();
    }
    protected function getPluralRuleset() : \RectorPrefix20210909\Doctrine\Inflector\Rules\Ruleset
    {
        return \RectorPrefix20210909\Doctrine\Inflector\Rules\Portuguese\Rules::getPluralRuleset();
    }
}
