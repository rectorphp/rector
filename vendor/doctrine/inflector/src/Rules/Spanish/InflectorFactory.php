<?php

declare (strict_types=1);
namespace RectorPrefix202512\Doctrine\Inflector\Rules\Spanish;

use RectorPrefix202512\Doctrine\Inflector\GenericLanguageInflectorFactory;
use RectorPrefix202512\Doctrine\Inflector\Rules\Ruleset;
final class InflectorFactory extends GenericLanguageInflectorFactory
{
    protected function getSingularRuleset(): Ruleset
    {
        return Rules::getSingularRuleset();
    }
    protected function getPluralRuleset(): Ruleset
    {
        return Rules::getPluralRuleset();
    }
}
