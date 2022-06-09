<?php

declare (strict_types=1);
namespace RectorPrefix20220609\Doctrine\Inflector\Rules\Portuguese;

use RectorPrefix20220609\Doctrine\Inflector\Rules\Patterns;
use RectorPrefix20220609\Doctrine\Inflector\Rules\Ruleset;
use RectorPrefix20220609\Doctrine\Inflector\Rules\Substitutions;
use RectorPrefix20220609\Doctrine\Inflector\Rules\Transformations;
final class Rules
{
    public static function getSingularRuleset() : Ruleset
    {
        return new Ruleset(new Transformations(...Inflectible::getSingular()), new Patterns(...Uninflected::getSingular()), (new Substitutions(...Inflectible::getIrregular()))->getFlippedSubstitutions());
    }
    public static function getPluralRuleset() : Ruleset
    {
        return new Ruleset(new Transformations(...Inflectible::getPlural()), new Patterns(...Uninflected::getPlural()), new Substitutions(...Inflectible::getIrregular()));
    }
}
