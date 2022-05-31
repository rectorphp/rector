<?php

declare (strict_types=1);
namespace Rector\Core\Configuration;

use Rector\Caching\Contract\ValueObject\Storage\CacheStorageInterface;
use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use RectorPrefix20220531\Symplify\Skipper\ValueObject\Option as SkipperOption;
final class Option
{
    /**
     * @var string
     */
    public const SOURCE = 'source';
    /**
     * @var string
     */
    public const AUTOLOAD_FILE = 'autoload-file';
    /**
     * @deprecated Use @see \Rector\Config\RectorConfig::bootstrapFiles() instead
     * @var string
     */
    public const BOOTSTRAP_FILES = 'bootstrap_files';
    /**
     * @var string
     */
    public const DRY_RUN = 'dry-run';
    /**
     * @var string
     */
    public const DRY_RUN_SHORT = 'n';
    /**
     * @var string
     */
    public const OUTPUT_FORMAT = 'output-format';
    /**
     * @var string
     */
    public const NO_PROGRESS_BAR = 'no-progress-bar';
    /**
     * @deprecated Use @see \Rector\Config\RectorConfig::phpVersion() instead
     * @var string
     */
    public const PHP_VERSION_FEATURES = 'php_version_features';
    /**
     * @deprecated Use @see \Rector\Config\RectorConfig::importNames() instead
     * @var string
     */
    public const AUTO_IMPORT_NAMES = 'auto_import_names';
    /**
     * @deprecated Use @see \Rector\Config\RectorConfig::importShortClasses() instead
     * @var string
     */
    public const IMPORT_SHORT_CLASSES = 'import_short_classes';
    /**
     * @deprecated Use @see \Rector\Config\RectorConfig::symfonyContainerXml() instead
     * @var string
     */
    public const SYMFONY_CONTAINER_XML_PATH_PARAMETER = 'symfony_container_xml_path';
    /**
     * @deprecated Use @see \Rector\Config\RectorConfig::symfonyContainerPhp()
     * @var string
     */
    public const SYMFONY_CONTAINER_PHP_PATH_PARAMETER = 'symfony_container_php_path';
    /**
     * @var string
     */
    public const CLEAR_CACHE = 'clear-cache';
    /**
     * @deprecated Use @see \Rector\Config\RectorConfig::parallel() instead
     * @var string
     */
    public const PARALLEL = 'parallel';
    /**
     * @deprecated Use @see \Rector\Config\RectorConfig::paths() instead
     * @var string
     */
    public const PATHS = 'paths';
    /**
     * @deprecated Use @see \Rector\Config\RectorConfig::autoloadPaths() instead
     * @var string
     */
    public const AUTOLOAD_PATHS = 'autoload_paths';
    /**
     * @deprecated Use @see \Rector\Config\RectorConfig::skip() instead
     * @var string
     */
    public const SKIP = \RectorPrefix20220531\Symplify\Skipper\ValueObject\Option::SKIP;
    /**
     * @deprecated Use RectorConfig::fileExtensions() instead
     * @var string
     */
    public const FILE_EXTENSIONS = 'file_extensions';
    /**
     * @deprecated Use RectorConfig::nestedChainMethodCallLimit() instead
     * @var string
     */
    public const NESTED_CHAIN_METHOD_CALL_LIMIT = 'nested_chain_method_call_limit';
    /**
     * @deprecated Use RectorConfig::cacheDirectory() instead
     * @var string
     */
    public const CACHE_DIR = 'cache_dir';
    /**
     * Cache backend. Most of the time we cache in files, but in ephemeral environment (e.g. CI), a faster `MemoryCacheStorage` can be usefull.
     * @deprecated Use RectorConfig::cacheClass() instead
     *
     * @var class-string<CacheStorageInterface>
     * @internal
     */
    public const CACHE_CLASS = \Rector\Caching\ValueObject\Storage\FileCacheStorage::class;
    /**
     * @var string
     */
    public const DEBUG = 'debug';
    /**
     * @var string
     */
    public const XDEBUG = 'xdebug';
    /**
     * @var string
     */
    public const CONFIG = 'config';
    /**
     * @deprecated Use @see \Rector\Config\RectorConfig::phpstanConfig() instead
     * @var string
     */
    public const PHPSTAN_FOR_RECTOR_PATH = 'phpstan_for_rector_path';
    /**
     * @var string
     */
    public const NO_DIFFS = 'no-diffs';
    /**
     * @var string
     */
    public const TEMPLATE_TYPE = 'template-type';
    /**
     * @var string
     */
    public const ENABLE_EDITORCONFIG = 'enable_editorconfig';
    /**
     * @var string
     */
    public const AUTOLOAD_FILE_SHORT = 'a';
    /**
     * @var string
     */
    public const OUTPUT_FORMAT_SHORT = 'o';
    /**
     * @var string
     */
    public const APPLY_AUTO_IMPORT_NAMES_ON_CHANGED_FILES_ONLY = 'apply_auto_import_names_on_changed_files_only';
    /**
     * @var string
     */
    public const PARALLEL_IDENTIFIER = 'identifier';
    /**
     * @var string
     */
    public const PARALLEL_PORT = 'port';
    /**
     * @deprecated Use @see \Rector\Config\RectorConfig::parallel() instead with pass int $jobSize parameter
     * @var string
     */
    public const PARALLEL_JOB_SIZE = 'parallel-job-size';
    /**
     * @deprecated Use @see \Rector\Config\RectorConfig::parallel() instead with pass int $maxNumberOfProcess parameter
     * @var string
     */
    public const PARALLEL_MAX_NUMBER_OF_PROCESSES = 'parallel-max-number-of-processes';
    /**
     * @deprecated Use @see \Rector\Config\RectorConfig::parallel() instead with pass int $seconds parameter
     * @var string
     */
    public const PARALLEL_TIMEOUT_IN_SECONDS = 'parallel-timeout-in-seconds';
    /**
     * @var string
     */
    public const FOLLOW_SYMLINKS = 'follow-symlinks';
    /**
     * @var string
     */
    public const MEMORY_LIMIT = 'memory-limit';
}
