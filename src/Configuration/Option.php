<?php

declare (strict_types=1);
namespace Rector\Configuration;

use Rector\Caching\Contract\ValueObject\Storage\CacheStorageInterface;
use Rector\Caching\ValueObject\Storage\FileCacheStorage;
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
     * @internal Use @see \Rector\Config\RectorConfig::bootstrapFiles() instead
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
     * @internal Use @see \Rector\Config\RectorConfig::phpVersion() instead
     * @var string
     */
    public const PHP_VERSION_FEATURES = 'php_version_features';
    /**
     * @internal Use @see \Rector\Config\RectorConfig::importNames() instead
     * @var string
     */
    public const AUTO_IMPORT_NAMES = 'auto_import_names';
    /**
     * @internal Use @see \Rector\Config\RectorConfig::polyfillPackages() instead
     * @var string
     */
    public const POLYFILL_PACKAGES = 'polyfill_packages';
    /**
     * @internal Use @see \Rector\Config\RectorConfig::importNames() instead
     * @var string
     */
    public const AUTO_IMPORT_DOC_BLOCK_NAMES = 'auto_import_doc_block_names';
    /**
     * @internal Use @see \Rector\Config\RectorConfig::importShortClasses() instead
     * @var string
     */
    public const IMPORT_SHORT_CLASSES = 'import_short_classes';
    /**
     * @internal Use @see \Rector\Config\RectorConfig::symfonyContainerXml() instead
     * @var string
     */
    public const SYMFONY_CONTAINER_XML_PATH_PARAMETER = 'symfony_container_xml_path';
    /**
     * @internal Use @see \Rector\Config\RectorConfig::symfonyContainerPhp()
     * @var string
     */
    public const SYMFONY_CONTAINER_PHP_PATH_PARAMETER = 'symfony_container_php_path';
    /**
     * @internal Use @see \Rector\Config\RectorConfig::newLineOnFluentCall()
     * @var string
     */
    public const NEW_LINE_ON_FLUENT_CALL = 'new_line_on_fluent_call';
    /**
     * @var string
     */
    public const CLEAR_CACHE = 'clear-cache';
    /**
     * @internal Use @see \Rector\Config\RectorConfig::parallel() instead
     * @var string
     */
    public const PARALLEL = 'parallel';
    /**
     * @internal Use @see \Rector\Config\RectorConfig::paths() instead
     * @var string
     */
    public const PATHS = 'paths';
    /**
     * @internal Use @see \Rector\Config\RectorConfig::autoloadPaths() instead
     * @var string
     */
    public const AUTOLOAD_PATHS = 'autoload_paths';
    /**
     * @internal Use @see \Rector\Config\RectorConfig::skip() instead
     * @var string
     */
    public const SKIP = 'skip';
    /**
     * @internal Use RectorConfig::fileExtensions() instead
     * @var string
     */
    public const FILE_EXTENSIONS = 'file_extensions';
    /**
     * @internal Use RectorConfig::cacheDirectory() instead
     * @var string
     */
    public const CACHE_DIR = 'cache_dir';
    /**
     * Cache backend. Most of the time we cache in files, but in ephemeral environment (e.g. CI), a faster `MemoryCacheStorage` can be usefull.
     * @internal Use RectorConfig::cacheClass() instead
     *
     * @var class-string<CacheStorageInterface>
     * @internal
     */
    public const CACHE_CLASS = FileCacheStorage::class;
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
     * @internal Use @see \Rector\Config\RectorConfig::phpstanConfig() instead
     * @var string
     */
    public const PHPSTAN_FOR_RECTOR_PATHS = 'phpstan_for_rector_paths';
    /**
     * @var string
     */
    public const NO_DIFFS = 'no-diffs';
    /**
     * @var string
     */
    public const AUTOLOAD_FILE_SHORT = 'a';
    /**
     * @var string
     */
    public const PARALLEL_IDENTIFIER = 'identifier';
    /**
     * @var string
     */
    public const PARALLEL_PORT = 'port';
    /**
     * @internal Use @see \Rector\Config\RectorConfig::parallel() instead with pass int $jobSize parameter
     * @var string
     */
    public const PARALLEL_JOB_SIZE = 'parallel-job-size';
    /**
     * @internal Use @see \Rector\Config\RectorConfig::parallel() instead with pass int $maxNumberOfProcess parameter
     * @var string
     */
    public const PARALLEL_MAX_NUMBER_OF_PROCESSES = 'parallel-max-number-of-processes';
    /**
     * @internal Use @see \Rector\Config\RectorConfig::parallel() instead with pass int $seconds parameter
     * @var string
     */
    public const PARALLEL_JOB_TIMEOUT_IN_SECONDS = 'parallel-job-timeout-in-seconds';
    /**
     * @var string
     */
    public const MEMORY_LIMIT = 'memory-limit';
    /**
     * @internal Use @see \Rector\Config\RectorConfig::indent() method
     * @var string
     */
    public const INDENT_CHAR = 'indent-char';
    /**
     * @internal Use @see \Rector\Config\RectorConfig::indent() method
     * @var string
     */
    public const INDENT_SIZE = 'indent-size';
    /**
     * @internal Use @see \Rector\Config\RectorConfig::removeUnusedImports() method
     * @var string
     */
    public const REMOVE_UNUSED_IMPORTS = 'remove-unused-imports';
    /**
     * @internal Use @see \Rector\Config\RectorConfig::containerCacheDirectory() method
     * @var string
     */
    public const CONTAINER_CACHE_DIRECTORY = 'container-cache-directory';
    /**
     * @internal For cache invalidation in case of change
     * @var string
     */
    public const REGISTERED_RECTOR_RULES = 'registered_rector_rules';
    /**
     * @internal For cache invalidation in case of change
     * @var string
     */
    public const REGISTERED_RECTOR_SETS = 'registered_rector_sets';
    /**
     * @internal For verify skipped rules exists in registered rules
     * @var string
     */
    public const SKIPPED_RECTOR_RULES = 'skipped_rector_rules';
    /**
     * @internal For collect skipped start with short open tag files to be reported
     * @var string
     */
    public const SKIPPED_START_WITH_SHORT_OPEN_TAG_FILES = 'skipped_start_with_short_open_tag_files';
    /**
     * @internal For reporting with absolute paths instead of relative paths (default behaviour)
     * @see \Rector\Config\RectorConfig::reportingRealPath()
     * @var string
     */
    public const ABSOLUTE_FILE_PATH = 'absolute_file_path';
    /**
     * @internal To add editor links to console output
     * @see \Rector\Config\RectorConfig::editorUrl()
     * @var string
     */
    public const EDITOR_URL = 'editor_url';
}
