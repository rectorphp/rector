<?php

declare (strict_types=1);
namespace RectorPrefix20220219\Doctrine\Inflector\Rules\English;

use RectorPrefix20220219\Doctrine\Inflector\GenericLanguageInflectorFactory;
use RectorPrefix20220219\Doctrine\Inflector\Rules\Ruleset;
final class InflectorFactory extends \RectorPrefix20220219\Doctrine\Inflector\GenericLanguageInflectorFactory
{
    protected function getSingularRuleset() : \RectorPrefix20220219\Doctrine\Inflector\Rules\Ruleset
    {
        return \RectorPrefix20220219\Doctrine\Inflector\Rules\English\Rules::getSingularRuleset();
    }
    protected function getPluralRuleset() : \RectorPrefix20220219\Doctrine\Inflector\Rules\Ruleset
    {
        return \RectorPrefix20220219\Doctrine\Inflector\Rules\English\Rules::getPluralRuleset();
    }
}
