<?php

declare (strict_types=1);
namespace RectorPrefix20211020\Doctrine\Inflector\Rules\French;

use RectorPrefix20211020\Doctrine\Inflector\Rules\Patterns;
use RectorPrefix20211020\Doctrine\Inflector\Rules\Ruleset;
use RectorPrefix20211020\Doctrine\Inflector\Rules\Substitutions;
use RectorPrefix20211020\Doctrine\Inflector\Rules\Transformations;
final class Rules
{
    public static function getSingularRuleset() : \RectorPrefix20211020\Doctrine\Inflector\Rules\Ruleset
    {
        return new \RectorPrefix20211020\Doctrine\Inflector\Rules\Ruleset(new \RectorPrefix20211020\Doctrine\Inflector\Rules\Transformations(...\RectorPrefix20211020\Doctrine\Inflector\Rules\French\Inflectible::getSingular()), new \RectorPrefix20211020\Doctrine\Inflector\Rules\Patterns(...\RectorPrefix20211020\Doctrine\Inflector\Rules\French\Uninflected::getSingular()), (new \RectorPrefix20211020\Doctrine\Inflector\Rules\Substitutions(...\RectorPrefix20211020\Doctrine\Inflector\Rules\French\Inflectible::getIrregular()))->getFlippedSubstitutions());
    }
    public static function getPluralRuleset() : \RectorPrefix20211020\Doctrine\Inflector\Rules\Ruleset
    {
        return new \RectorPrefix20211020\Doctrine\Inflector\Rules\Ruleset(new \RectorPrefix20211020\Doctrine\Inflector\Rules\Transformations(...\RectorPrefix20211020\Doctrine\Inflector\Rules\French\Inflectible::getPlural()), new \RectorPrefix20211020\Doctrine\Inflector\Rules\Patterns(...\RectorPrefix20211020\Doctrine\Inflector\Rules\French\Uninflected::getPlural()), new \RectorPrefix20211020\Doctrine\Inflector\Rules\Substitutions(...\RectorPrefix20211020\Doctrine\Inflector\Rules\French\Inflectible::getIrregular()));
    }
}
