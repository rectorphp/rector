<?php

declare (strict_types=1);
namespace RectorPrefix20220209\Doctrine\Inflector\Rules\Spanish;

use RectorPrefix20220209\Doctrine\Inflector\Rules\Patterns;
use RectorPrefix20220209\Doctrine\Inflector\Rules\Ruleset;
use RectorPrefix20220209\Doctrine\Inflector\Rules\Substitutions;
use RectorPrefix20220209\Doctrine\Inflector\Rules\Transformations;
final class Rules
{
    public static function getSingularRuleset() : \RectorPrefix20220209\Doctrine\Inflector\Rules\Ruleset
    {
        return new \RectorPrefix20220209\Doctrine\Inflector\Rules\Ruleset(new \RectorPrefix20220209\Doctrine\Inflector\Rules\Transformations(...\RectorPrefix20220209\Doctrine\Inflector\Rules\Spanish\Inflectible::getSingular()), new \RectorPrefix20220209\Doctrine\Inflector\Rules\Patterns(...\RectorPrefix20220209\Doctrine\Inflector\Rules\Spanish\Uninflected::getSingular()), (new \RectorPrefix20220209\Doctrine\Inflector\Rules\Substitutions(...\RectorPrefix20220209\Doctrine\Inflector\Rules\Spanish\Inflectible::getIrregular()))->getFlippedSubstitutions());
    }
    public static function getPluralRuleset() : \RectorPrefix20220209\Doctrine\Inflector\Rules\Ruleset
    {
        return new \RectorPrefix20220209\Doctrine\Inflector\Rules\Ruleset(new \RectorPrefix20220209\Doctrine\Inflector\Rules\Transformations(...\RectorPrefix20220209\Doctrine\Inflector\Rules\Spanish\Inflectible::getPlural()), new \RectorPrefix20220209\Doctrine\Inflector\Rules\Patterns(...\RectorPrefix20220209\Doctrine\Inflector\Rules\Spanish\Uninflected::getPlural()), new \RectorPrefix20220209\Doctrine\Inflector\Rules\Substitutions(...\RectorPrefix20220209\Doctrine\Inflector\Rules\Spanish\Inflectible::getIrregular()));
    }
}
