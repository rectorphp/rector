<?php

declare (strict_types=1);
namespace RectorPrefix20220501\Doctrine\Inflector\Rules\NorwegianBokmal;

use RectorPrefix20220501\Doctrine\Inflector\Rules\Patterns;
use RectorPrefix20220501\Doctrine\Inflector\Rules\Ruleset;
use RectorPrefix20220501\Doctrine\Inflector\Rules\Substitutions;
use RectorPrefix20220501\Doctrine\Inflector\Rules\Transformations;
final class Rules
{
    public static function getSingularRuleset() : \RectorPrefix20220501\Doctrine\Inflector\Rules\Ruleset
    {
        return new \RectorPrefix20220501\Doctrine\Inflector\Rules\Ruleset(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformations(...\RectorPrefix20220501\Doctrine\Inflector\Rules\NorwegianBokmal\Inflectible::getSingular()), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Patterns(...\RectorPrefix20220501\Doctrine\Inflector\Rules\NorwegianBokmal\Uninflected::getSingular()), (new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitutions(...\RectorPrefix20220501\Doctrine\Inflector\Rules\NorwegianBokmal\Inflectible::getIrregular()))->getFlippedSubstitutions());
    }
    public static function getPluralRuleset() : \RectorPrefix20220501\Doctrine\Inflector\Rules\Ruleset
    {
        return new \RectorPrefix20220501\Doctrine\Inflector\Rules\Ruleset(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformations(...\RectorPrefix20220501\Doctrine\Inflector\Rules\NorwegianBokmal\Inflectible::getPlural()), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Patterns(...\RectorPrefix20220501\Doctrine\Inflector\Rules\NorwegianBokmal\Uninflected::getPlural()), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitutions(...\RectorPrefix20220501\Doctrine\Inflector\Rules\NorwegianBokmal\Inflectible::getIrregular()));
    }
}
