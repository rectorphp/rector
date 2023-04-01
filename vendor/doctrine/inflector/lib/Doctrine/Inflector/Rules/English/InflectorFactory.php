<?php

declare (strict_types=1);
namespace RectorPrefix202304\Doctrine\Inflector\Rules\English;

use RectorPrefix202304\Doctrine\Inflector\GenericLanguageInflectorFactory;
use RectorPrefix202304\Doctrine\Inflector\Rules\Ruleset;
final class InflectorFactory extends GenericLanguageInflectorFactory
{
    protected function getSingularRuleset() : Ruleset
    {
        return Rules::getSingularRuleset();
    }
    protected function getPluralRuleset() : Ruleset
    {
        return Rules::getPluralRuleset();
    }
}
