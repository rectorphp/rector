<?php

declare (strict_types=1);
namespace RectorPrefix202312\Doctrine\Inflector\Rules\Turkish;

use RectorPrefix202312\Doctrine\Inflector\GenericLanguageInflectorFactory;
use RectorPrefix202312\Doctrine\Inflector\Rules\Ruleset;
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
