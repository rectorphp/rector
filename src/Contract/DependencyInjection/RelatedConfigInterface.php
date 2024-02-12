<?php

declare (strict_types=1);
namespace Rector\Contract\DependencyInjection;

/**
 * @internal Use for rules that require extra custom services.
 */
interface RelatedConfigInterface
{
    public static function getConfigFile() : string;
}
