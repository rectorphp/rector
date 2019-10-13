<?php

declare(strict_types=1);

namespace Rector\Configuration;

final class Option
{
    /**
     * @var string
     */
    public const SOURCE = 'source';

    /**
     * @var string
     */
    public const OPTION_AUTOLOAD_FILE = 'autoload-file';

    /**
     * @var string
     */
    public const OPTION_DRY_RUN = 'dry-run';

    /**
     * @var string
     */
    public const OPTION_OUTPUT_FORMAT = 'output-format';

    /**
     * @var string
     */
    public const KERNEL_CLASS_PARAMETER = 'kernel_class';

    /**
     * @var string
     */
    public const KERNEL_ENVIRONMENT_PARAMETER = 'kernel_environment';

    /**
     * @var string
     */
    public const PHP_VERSION_FEATURES = 'php_version_features';

    /**
     * @var string
     */
    public const HIDE_AUTOLOAD_ERRORS = 'hide-autoload-errors';

    /**
     * @var string
     */
    public const OPTION_RULE = 'rule';

    /**
     * @var string
     */
    public const AUTO_IMPORT_NAMES = 'auto_import_names';
}
