<?php

declare (strict_types=1);
namespace Rector\Configuration\Deprecation\Contract;

/**
 * @api
 *
 * Marker interface that should be implemented by all deprecated rules and services.
 * It helps to notify user about the deprecation on the fly, before they get removed.
 */
interface DeprecatedInterface
{
}
