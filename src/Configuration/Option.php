<?php

declare(strict_types=1);

namespace Rector\Core\Configuration;

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
    public const OPTION_NO_PROGRESS_BAR = 'no-progress-bar';

    /**
     * @var string
     */
    public const PHP_VERSION_FEATURES = 'php_version_features';

    /**
     * @var string
     */
    public const OPTION_ONLY = 'only';

    /**
     * @var string
     */
    public const AUTO_IMPORT_NAMES = 'auto_import_names';

    /**
     * @var string
     */
    public const SAFE_TYPES = 'safe_types';

    /**
     * @var string
     */
    public const IMPORT_SHORT_CLASSES = 'import_short_classes';

    /**
     * @var string
     */
    public const EXCLUDE_RECTORS = 'exclude_rectors';

    /**
     * @var string
     */
    public const IMPORT_DOC_BLOCKS = 'import_doc_blocks';

    /**
     * @var string
     */
    public const MATCH_GIT_DIFF = 'match-git-diff';

    /**
     * @var string
     */
    public const SYMFONY_CONTAINER_XML_PATH_PARAMETER = 'symfony_container_xml_path';

    /**
     * @var string
     */
    public const OPTION_OUTPUT_FILE = 'output-file';

    /**
     * @var string
     */
    public const OPTION_CLEAR_CACHE = 'clear-cache';

    /**
     * @var string
     */
    public const PROJECT_TYPE = 'project_type';

    /**
     * @var string
     */
    public const PROJECT_TYPE_OPEN_SOURCE = 'open-source';

    /**
     * @var string
     */
    public const PROJECT_TYPE_PROPRIETARY = 'proprietary';

    /**
     * @var string
     */
    public const PROJECT_TYPE_OPEN_SOURCE_UNDESCORED = 'open_source';

    /**
     * @var string
     */
    public const CACHE_DEBUG = 'cache-debug';

    /**
     * @var string
     */
    public const PATHS = 'paths';

    /**
     * @var string
     */
    public const AUTOLOAD_PATHS = 'autoload_paths';

    /**
     * @var string
     */
    public const EXCLUDE_PATHS = 'exclude_paths';

    /**
     * @var string
     */
    public const SETS = 'sets';

    /**
     * @var string
     */
    public const FILE_EXTENSIONS = 'file_extensions';

    /**
     * @var string
     */
    public const RECTOR_RECIPE = 'rector_recipe';

    /**
     * @var string
     */
    public const NESTED_CHAIN_METHOD_CALL_LIMIT = 'nested_chain_method_call_limit';
}
