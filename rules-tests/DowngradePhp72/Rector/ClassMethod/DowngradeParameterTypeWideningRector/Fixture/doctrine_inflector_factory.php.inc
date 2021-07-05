<?php

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\LanguageInflectorFactory;
use Doctrine\Inflector\Rules\Ruleset;

final class DoctrineInflectorFactory implements LanguageInflectorFactory
{
    public function withSingularRules(?Ruleset $singularRules, bool $reset = false): LanguageInflectorFactory
    {
    }

    public function withPluralRules(?Ruleset $pluralRules, bool $reset = false): LanguageInflectorFactory
    {
    }

    public function build(): Inflector
    {
    }
}
