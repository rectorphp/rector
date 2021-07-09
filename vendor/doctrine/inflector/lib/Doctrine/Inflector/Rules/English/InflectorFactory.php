<?php

declare (strict_types=1);
namespace RectorPrefix20210709\Doctrine\Inflector\Rules\English;

use RectorPrefix20210709\Doctrine\Inflector\GenericLanguageInflectorFactory;
use RectorPrefix20210709\Doctrine\Inflector\Rules\Ruleset;
final class InflectorFactory extends \RectorPrefix20210709\Doctrine\Inflector\GenericLanguageInflectorFactory
{
    protected function getSingularRuleset() : \RectorPrefix20210709\Doctrine\Inflector\Rules\Ruleset
    {
        return \RectorPrefix20210709\Doctrine\Inflector\Rules\English\Rules::getSingularRuleset();
    }
    protected function getPluralRuleset() : \RectorPrefix20210709\Doctrine\Inflector\Rules\Ruleset
    {
        return \RectorPrefix20210709\Doctrine\Inflector\Rules\English\Rules::getPluralRuleset();
    }
}
