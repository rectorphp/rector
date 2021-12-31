<?php

declare (strict_types=1);
namespace RectorPrefix20211231\Doctrine\Inflector\Rules\Turkish;

use RectorPrefix20211231\Doctrine\Inflector\Rules\Patterns;
use RectorPrefix20211231\Doctrine\Inflector\Rules\Ruleset;
use RectorPrefix20211231\Doctrine\Inflector\Rules\Substitutions;
use RectorPrefix20211231\Doctrine\Inflector\Rules\Transformations;
final class Rules
{
    public static function getSingularRuleset() : \RectorPrefix20211231\Doctrine\Inflector\Rules\Ruleset
    {
        return new \RectorPrefix20211231\Doctrine\Inflector\Rules\Ruleset(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformations(...\RectorPrefix20211231\Doctrine\Inflector\Rules\Turkish\Inflectible::getSingular()), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Patterns(...\RectorPrefix20211231\Doctrine\Inflector\Rules\Turkish\Uninflected::getSingular()), (new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitutions(...\RectorPrefix20211231\Doctrine\Inflector\Rules\Turkish\Inflectible::getIrregular()))->getFlippedSubstitutions());
    }
    public static function getPluralRuleset() : \RectorPrefix20211231\Doctrine\Inflector\Rules\Ruleset
    {
        return new \RectorPrefix20211231\Doctrine\Inflector\Rules\Ruleset(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformations(...\RectorPrefix20211231\Doctrine\Inflector\Rules\Turkish\Inflectible::getPlural()), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Patterns(...\RectorPrefix20211231\Doctrine\Inflector\Rules\Turkish\Uninflected::getPlural()), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitutions(...\RectorPrefix20211231\Doctrine\Inflector\Rules\Turkish\Inflectible::getIrregular()));
    }
}
