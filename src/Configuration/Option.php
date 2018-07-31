<?php declare(strict_types=1);

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
    public const OPTION_NO_DIFFS = 'no-diff';

    /**
     * @var string
     */
    public const OPTION_WITH_STYLE = 'with-style';

    /**
     * @var string
     */
    public const OPTION_LEVEL = 'level';

    /**
     * @var string
     */
    public const KERNEL_CLASS_PARAMETER = 'kernel_class';
}
