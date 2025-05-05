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
    public const TWIG = 'twig';
    /**
     * Version-based set provider
     * @var string
     */
    public const PHPUNIT = 'phpunit';
    /**
     * Version-based set provider
     * @var string
     */
    public const DOCTRINE = 'doctrine';
    /**
     * Version-based set provider
     * @var string
     */
    public const SYMFONY = 'symfony';
    /**
     * Version-based set provider
     * @var string
     */
    public const NETTE_UTILS = 'nette-utils';
    /**
     * Version-based set provider
     * @var string
     */
    public const LARAVEL = 'laravel';
    /**
     * @var string
     */
    public const ATTRIBUTES = 'attributes';
}
