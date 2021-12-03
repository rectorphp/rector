<?php

declare (strict_types=1);
namespace RectorPrefix20211203\Doctrine\Inflector\Rules\English;

use RectorPrefix20211203\Doctrine\Inflector\GenericLanguageInflectorFactory;
use RectorPrefix20211203\Doctrine\Inflector\Rules\Ruleset;
final class InflectorFactory extends \RectorPrefix20211203\Doctrine\Inflector\GenericLanguageInflectorFactory
{
    protected function getSingularRuleset() : \RectorPrefix20211203\Doctrine\Inflector\Rules\Ruleset
    {
        return \RectorPrefix20211203\Doctrine\Inflector\Rules\English\Rules::getSingularRuleset();
    }
    protected function getPluralRuleset() : \RectorPrefix20211203\Doctrine\Inflector\Rules\Ruleset
    {
        return \RectorPrefix20211203\Doctrine\Inflector\Rules\English\Rules::getPluralRuleset();
    }
}
