<?php

declare (strict_types=1);
namespace RectorPrefix20220418\Symplify\ComposerJsonManipulator\ValueObject;

/**
 * @api
 */
final class ComposerJsonSection
{
    /**
     * @var string
     */
    public const REPOSITORIES = 'repositories';
    /**
     * @var string
     */
    public const REQUIRE_DEV = 'require-dev';
    /**
     * @var string
     */
    public const REQUIRE = 'require';
    /**
     * @var string
     */
    public const CONFLICT = 'conflict';
    /**
     * @var string
     */
    public const PREFER_STABLE = 'prefer-stable';
    /**
     * @var string
     */
    public const MINIMUM_STABILITY = 'minimum-stability';
    /**
     * @var string
     */
    public const AUTOLOAD = 'autoload';
    /**
     * @var string
     */
    public const AUTOLOAD_DEV = 'autoload-dev';
    /**
     * @var string
     */
    public const REPLACE = 'replace';
    /**
     * @var string
     */
    public const CONFIG = 'config';
    /**
     * @var string
     */
    public const EXTRA = 'extra';
    /**
     * @var string
     */
    public const NAME = 'name';
    /**
     * @var string
     */
    public const DESCRIPTION = 'description';
    /**
     * @var string
     */
    public const KEYWORDS = 'keywords';
    /**
     * @var string
     */
    public const HOMEPAGE = 'homepage';
    /**
     * @var string
     */
    public const LICENSE = 'license';
    /**
     * @var string
     */
    public const SCRIPTS = 'scripts';
    /**
     * @var string
     */
    public const BIN = 'bin';
    /**
     * @var string
     */
    public const TYPE = 'type';
    /**
     * @var string
     */
    public const AUTHORS = 'authors';
    /**
     * @var string
     */
    public const PROVIDES = 'provides';
    /**
     * @var string
     * @see https://getcomposer.org/doc/articles/scripts.md#custom-descriptions-
     */
    public const SCRIPTS_DESCRIPTIONS = 'scripts-descriptions';
    /**
     * @var string
     */
    public const SUGGEST = 'suggest';
    /**
     * @var string
     */
    public const VERSION = 'version';
}
