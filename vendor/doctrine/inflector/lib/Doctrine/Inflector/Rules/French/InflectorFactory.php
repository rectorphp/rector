<?php

declare (strict_types=1);
namespace RectorPrefix20211020\Doctrine\Inflector\Rules\French;

use RectorPrefix20211020\Doctrine\Inflector\GenericLanguageInflectorFactory;
use RectorPrefix20211020\Doctrine\Inflector\Rules\Ruleset;
final class InflectorFactory extends \RectorPrefix20211020\Doctrine\Inflector\GenericLanguageInflectorFactory
{
    protected function getSingularRuleset() : \RectorPrefix20211020\Doctrine\Inflector\Rules\Ruleset
    {
        return \RectorPrefix20211020\Doctrine\Inflector\Rules\French\Rules::getSingularRuleset();
    }
    protected function getPluralRuleset() : \RectorPrefix20211020\Doctrine\Inflector\Rules\Ruleset
    {
        return \RectorPrefix20211020\Doctrine\Inflector\Rules\French\Rules::getPluralRuleset();
    }
}
