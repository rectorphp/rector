<?php

declare (strict_types=1);
namespace RectorPrefix20210827\Doctrine\Inflector\Rules\Turkish;

use RectorPrefix20210827\Doctrine\Inflector\Rules\Patterns;
use RectorPrefix20210827\Doctrine\Inflector\Rules\Ruleset;
use RectorPrefix20210827\Doctrine\Inflector\Rules\Substitutions;
use RectorPrefix20210827\Doctrine\Inflector\Rules\Transformations;
final class Rules
{
    public static function getSingularRuleset() : \RectorPrefix20210827\Doctrine\Inflector\Rules\Ruleset
    {
        return new \RectorPrefix20210827\Doctrine\Inflector\Rules\Ruleset(new \RectorPrefix20210827\Doctrine\Inflector\Rules\Transformations(...\RectorPrefix20210827\Doctrine\Inflector\Rules\Turkish\Inflectible::getSingular()), new \RectorPrefix20210827\Doctrine\Inflector\Rules\Patterns(...\RectorPrefix20210827\Doctrine\Inflector\Rules\Turkish\Uninflected::getSingular()), (new \RectorPrefix20210827\Doctrine\Inflector\Rules\Substitutions(...\RectorPrefix20210827\Doctrine\Inflector\Rules\Turkish\Inflectible::getIrregular()))->getFlippedSubstitutions());
    }
    public static function getPluralRuleset() : \RectorPrefix20210827\Doctrine\Inflector\Rules\Ruleset
    {
        return new \RectorPrefix20210827\Doctrine\Inflector\Rules\Ruleset(new \RectorPrefix20210827\Doctrine\Inflector\Rules\Transformations(...\RectorPrefix20210827\Doctrine\Inflector\Rules\Turkish\Inflectible::getPlural()), new \RectorPrefix20210827\Doctrine\Inflector\Rules\Patterns(...\RectorPrefix20210827\Doctrine\Inflector\Rules\Turkish\Uninflected::getPlural()), new \RectorPrefix20210827\Doctrine\Inflector\Rules\Substitutions(...\RectorPrefix20210827\Doctrine\Inflector\Rules\Turkish\Inflectible::getIrregular()));
    }
}
