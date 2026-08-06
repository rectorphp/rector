<?php

declare (strict_types=1);
namespace Rector\Set\Enum;

/**
 * @api used in sets
 */
final class SetGroup
{
    /**
     * @var string
     */
    public const CORE = 'core';
    /**
     * @var string
     */
    public const PHP = 'php';
    /**
     * Version-based set provider
     * @var string
     */
    public const LARAVEL = 'laravel';
    /**
     * Version-based set provider
     * @var string
     */
    public const DRUPAL = 'drupal';
    /**
     * @var string
     */
    public const ATTRIBUTES = 'attributes';
}
