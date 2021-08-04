<?php

declare (strict_types=1);
namespace RectorPrefix20210804\Doctrine\Inflector;

use RectorPrefix20210804\Doctrine\Inflector\Rules\Ruleset;
use function array_unshift;
abstract class GenericLanguageInflectorFactory implements \RectorPrefix20210804\Doctrine\Inflector\LanguageInflectorFactory
{
    /** @var Ruleset[] */
    private $singularRulesets = [];
    /** @var Ruleset[] */
    private $pluralRulesets = [];
    public final function __construct()
    {
        $this->singularRulesets[] = $this->getSingularRuleset();
        $this->pluralRulesets[] = $this->getPluralRuleset();
    }
    public final function build() : \RectorPrefix20210804\Doctrine\Inflector\Inflector
    {
        return new \RectorPrefix20210804\Doctrine\Inflector\Inflector(new \RectorPrefix20210804\Doctrine\Inflector\CachedWordInflector(new \RectorPrefix20210804\Doctrine\Inflector\RulesetInflector(...$this->singularRulesets)), new \RectorPrefix20210804\Doctrine\Inflector\CachedWordInflector(new \RectorPrefix20210804\Doctrine\Inflector\RulesetInflector(...$this->pluralRulesets)));
    }
    /**
     * @param \Doctrine\Inflector\Rules\Ruleset|null $singularRules
     * @param bool $reset
     */
    public final function withSingularRules($singularRules, $reset = \false)
    {
        if ($reset) {
            $this->singularRulesets = [];
        }
        if ($singularRules instanceof \RectorPrefix20210804\Doctrine\Inflector\Rules\Ruleset) {
            \array_unshift($this->singularRulesets, $singularRules);
        }
        return $this;
    }
    /**
     * @param \Doctrine\Inflector\Rules\Ruleset|null $pluralRules
     * @param bool $reset
     */
    public final function withPluralRules($pluralRules, $reset = \false)
    {
        if ($reset) {
            $this->pluralRulesets = [];
        }
        if ($pluralRules instanceof \RectorPrefix20210804\Doctrine\Inflector\Rules\Ruleset) {
            \array_unshift($this->pluralRulesets, $pluralRules);
        }
        return $this;
    }
    protected abstract function getSingularRuleset() : \RectorPrefix20210804\Doctrine\Inflector\Rules\Ruleset;
    protected abstract function getPluralRuleset() : \RectorPrefix20210804\Doctrine\Inflector\Rules\Ruleset;
}
