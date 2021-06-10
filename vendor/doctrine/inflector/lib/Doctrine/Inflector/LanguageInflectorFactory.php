<?php

declare (strict_types=1);
namespace RectorPrefix20210610\Doctrine\Inflector;

use RectorPrefix20210610\Doctrine\Inflector\Rules\Ruleset;
interface LanguageInflectorFactory
{
    /**
     * Applies custom rules for singularisation
     *
     * @param bool $reset If true, will unset default inflections for all new rules
     *
     * @return $this
     */
    public function withSingularRules(?\RectorPrefix20210610\Doctrine\Inflector\Rules\Ruleset $singularRules, bool $reset = \false);
    /**
     * Applies custom rules for pluralisation
     *
     * @param bool $reset If true, will unset default inflections for all new rules
     *
     * @return $this
     */
    public function withPluralRules(?\RectorPrefix20210610\Doctrine\Inflector\Rules\Ruleset $pluralRules, bool $reset = \false);
    /**
     * Builds the inflector instance with all applicable rules
     */
    public function build() : \RectorPrefix20210610\Doctrine\Inflector\Inflector;
}
