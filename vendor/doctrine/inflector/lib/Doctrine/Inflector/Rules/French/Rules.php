<?php

declare (strict_types=1);
namespace RectorPrefix20220602\Doctrine\Inflector\Rules\French;

use RectorPrefix20220602\Doctrine\Inflector\Rules\Patterns;
use RectorPrefix20220602\Doctrine\Inflector\Rules\Ruleset;
use RectorPrefix20220602\Doctrine\Inflector\Rules\Substitutions;
use RectorPrefix20220602\Doctrine\Inflector\Rules\Transformations;
final class Rules
{
    public static function getSingularRuleset() : \RectorPrefix20220602\Doctrine\Inflector\Rules\Ruleset
    {
        return new \RectorPrefix20220602\Doctrine\Inflector\Rules\Ruleset(new \RectorPrefix20220602\Doctrine\Inflector\Rules\Transformations(...\RectorPrefix20220602\Doctrine\Inflector\Rules\French\Inflectible::getSingular()), new \RectorPrefix20220602\Doctrine\Inflector\Rules\Patterns(...\RectorPrefix20220602\Doctrine\Inflector\Rules\French\Uninflected::getSingular()), (new \RectorPrefix20220602\Doctrine\Inflector\Rules\Substitutions(...\RectorPrefix20220602\Doctrine\Inflector\Rules\French\Inflectible::getIrregular()))->getFlippedSubstitutions());
    }
    public static function getPluralRuleset() : \RectorPrefix20220602\Doctrine\Inflector\Rules\Ruleset
    {
        return new \RectorPrefix20220602\Doctrine\Inflector\Rules\Ruleset(new \RectorPrefix20220602\Doctrine\Inflector\Rules\Transformations(...\RectorPrefix20220602\Doctrine\Inflector\Rules\French\Inflectible::getPlural()), new \RectorPrefix20220602\Doctrine\Inflector\Rules\Patterns(...\RectorPrefix20220602\Doctrine\Inflector\Rules\French\Uninflected::getPlural()), new \RectorPrefix20220602\Doctrine\Inflector\Rules\Substitutions(...\RectorPrefix20220602\Doctrine\Inflector\Rules\French\Inflectible::getIrregular()));
    }
}
