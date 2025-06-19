<?php

declare (strict_types=1);
namespace Rector\Configuration\Parameter;

use Rector\Configuration\Option;
/**
 * Class to manage feature flags,
 * that loosen or tighten the behavior of Rector rules.
 */
final class FeatureFlags
{
    public static function treatClassesAsFinal() : bool
    {
        return \Rector\Configuration\Parameter\SimpleParameterProvider::provideBoolParameter(Option::TREAT_CLASSES_AS_FINAL);
    }
}
