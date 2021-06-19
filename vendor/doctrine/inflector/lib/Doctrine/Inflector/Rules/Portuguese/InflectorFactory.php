<?php

declare (strict_types=1);
namespace RectorPrefix20210619\Doctrine\Inflector\Rules\Portuguese;

use RectorPrefix20210619\Doctrine\Inflector\GenericLanguageInflectorFactory;
use RectorPrefix20210619\Doctrine\Inflector\Rules\Ruleset;
final class InflectorFactory extends \RectorPrefix20210619\Doctrine\Inflector\GenericLanguageInflectorFactory
{
    protected function getSingularRuleset() : \RectorPrefix20210619\Doctrine\Inflector\Rules\Ruleset
    {
        return \RectorPrefix20210619\Doctrine\Inflector\Rules\Portuguese\Rules::getSingularRuleset();
    }
    protected function getPluralRuleset() : \RectorPrefix20210619\Doctrine\Inflector\Rules\Ruleset
    {
        return \RectorPrefix20210619\Doctrine\Inflector\Rules\Portuguese\Rules::getPluralRuleset();
    }
}
