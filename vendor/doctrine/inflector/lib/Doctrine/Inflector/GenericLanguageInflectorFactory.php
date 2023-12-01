<?php

declare (strict_types=1);
namespace RectorPrefix202312\Doctrine\Inflector;

use RectorPrefix202312\Doctrine\Inflector\Rules\Ruleset;
use function array_unshift;
abstract class GenericLanguageInflectorFactory implements LanguageInflectorFactory
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
    public final function build() : Inflector
    {
        return new Inflector(new CachedWordInflector(new RulesetInflector(...$this->singularRulesets)), new CachedWordInflector(new RulesetInflector(...$this->pluralRulesets)));
    }
    public final function withSingularRules(?Ruleset $singularRules, bool $reset = \false) : LanguageInflectorFactory
    {
        if ($reset) {
            $this->singularRulesets = [];
        }
        if ($singularRules instanceof Ruleset) {
            array_unshift($this->singularRulesets, $singularRules);
        }
        return $this;
    }
    public final function withPluralRules(?Ruleset $pluralRules, bool $reset = \false) : LanguageInflectorFactory
    {
        if ($reset) {
            $this->pluralRulesets = [];
        }
        if ($pluralRules instanceof Ruleset) {
            array_unshift($this->pluralRulesets, $pluralRules);
        }
        return $this;
    }
    protected abstract function getSingularRuleset() : Ruleset;
    protected abstract function getPluralRuleset() : Ruleset;
}
