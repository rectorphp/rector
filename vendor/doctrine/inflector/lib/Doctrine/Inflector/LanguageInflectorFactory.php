<?php

declare (strict_types=1);
namespace RectorPrefix20211020\Doctrine\Inflector;

use RectorPrefix20211020\Doctrine\Inflector\Rules\Ruleset;
interface LanguageInflectorFactory
{
    /**
     * Applies custom rules for singularisation
     *
     * @param bool $reset If true, will unset default inflections for all new rules
     *
     * @return $this
     * @param \Doctrine\Inflector\Rules\Ruleset|null $singularRules
     */
    public function withSingularRules($singularRules, $reset = \false) : self;
    /**
     * Applies custom rules for pluralisation
     *
     * @param bool $reset If true, will unset default inflections for all new rules
     *
     * @return $this
     * @param \Doctrine\Inflector\Rules\Ruleset|null $pluralRules
     */
    public function withPluralRules($pluralRules, $reset = \false) : self;
    /**
     * Builds the inflector instance with all applicable rules
     */
    public function build() : \RectorPrefix20211020\Doctrine\Inflector\Inflector;
}
