<?php

declare (strict_types=1);
namespace RectorPrefix20210630\Doctrine\Inflector\Rules\Turkish;

use RectorPrefix20210630\Doctrine\Inflector\GenericLanguageInflectorFactory;
use RectorPrefix20210630\Doctrine\Inflector\Rules\Ruleset;
final class InflectorFactory extends \RectorPrefix20210630\Doctrine\Inflector\GenericLanguageInflectorFactory
{
    protected function getSingularRuleset() : \RectorPrefix20210630\Doctrine\Inflector\Rules\Ruleset
    {
        return \RectorPrefix20210630\Doctrine\Inflector\Rules\Turkish\Rules::getSingularRuleset();
    }
    protected function getPluralRuleset() : \RectorPrefix20210630\Doctrine\Inflector\Rules\Ruleset
    {
        return \RectorPrefix20210630\Doctrine\Inflector\Rules\Turkish\Rules::getPluralRuleset();
    }
}
