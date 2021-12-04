<?php

declare(strict_types=1);

namespace Rector\Core\Configuration;

use JetBrains\PhpStorm\Immutable;
use Rector\Caching\Contract\ValueObject\Storage\CacheStorageInterface;
use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Symplify\Skipper\ValueObject\Option as SkipperOption;

#[Immutable]
final class Option
{
    /**
     * @var string
     */
    final public const SOURCE = 'source';

    /**
     * @var string
     */
    final public const AUTOLOAD_FILE = 'autoload-file';

    /**
     * @var string
     */
    final public const BOOTSTRAP_FILES = 'bootstrap_files';

    /**
     * @var string
     */
    final public const DRY_RUN = 'dry-run';

    /**
     * @var string
     */
    final public const DRY_RUN_SHORT = 'n';

    /**
     * @var string
     */
    final public const OUTPUT_FORMAT = 'output-format';

    /**
     * @var string
     */
    final public const NO_PROGRESS_BAR = 'no-progress-bar';

    /**
     * @var string
     */
    final public const PHP_VERSION_FEATURES = 'php_version_features';

    /**
     * @var string
     */
    final public const AUTO_IMPORT_NAMES = 'auto_import_names';

    /**
     * @var string
     */
    final public const IMPORT_SHORT_CLASSES = 'import_short_classes';

    /**
     * @var string
     */
    final public const IMPORT_DOC_BLOCKS = 'import_doc_blocks';

    /**
     * @var string
     */
    final public const SYMFONY_CONTAINER_XML_PATH_PARAMETER = 'symfony_container_xml_path';

    /**
     * @var string
     */
    final public const CLEAR_CACHE = 'clear-cache';

    /**
     * @var string
     */
    final public const PARALLEL = 'parallel';

    /**
     * @deprecated Cache is enabled by default
     * @var string
     */
    final public const ENABLE_CACHE = 'enable_cache';

    /**
     * @var string
     */
    final public const PATHS = 'paths';

    /**
     * @var string
     */
    final public const AUTOLOAD_PATHS = 'autoload_paths';

    /**
     * @var string
     */
    final public const SKIP = SkipperOption::SKIP;

    /**
     * @var string
     */
    final public const FILE_EXTENSIONS = 'file_extensions';

    /**
     * @var string
     */
    final public const NESTED_CHAIN_METHOD_CALL_LIMIT = 'nested_chain_method_call_limit';

    /**
     * @var string
     */
    final public const CACHE_DIR = 'cache_dir';

    /**
     * Cache backend. Most of the time we cache in files, but in ephemeral environment (e.g. CI), a faster `MemoryCacheStorage` can be usefull.
     *
     * @var class-string<CacheStorageInterface>
     * @internal
     */
    final public const CACHE_CLASS = FileCacheStorage::class;

    /**
     * @var string
     */
    final public const DEBUG = 'debug';

    /**
     * @var string
     */
    final public const XDEBUG = 'xdebug';

    /**
     * @var string
     */
    final public const CONFIG = 'config';

    /**
     * @var string
     */
    final public const PHPSTAN_FOR_RECTOR_PATH = 'phpstan_for_rector_path';

    /**
     * @var string
     */
    final public const TYPES_TO_REMOVE_STATIC_FROM = 'types_to_remove_static_from';

    /**
     * @var string
     */
    final public const NO_DIFFS = 'no-diffs';

    /**
     * @var string
     */
    final public const TEMPLATE_TYPE = 'template-type';

    /**
     * @var string
     */
    final public const ENABLE_EDITORCONFIG = 'enable_editorconfig';

    /**
     * @var string
     */
    final public const AUTOLOAD_FILE_SHORT = 'a';

    /**
     * @var string
     */
    final public const OUTPUT_FORMAT_SHORT = 'o';

    /**
     * @var string
     */
    final public const APPLY_AUTO_IMPORT_NAMES_ON_CHANGED_FILES_ONLY = 'apply_auto_import_names_on_changed_files_only';

    /**
     * @var string
     */
    final public const PARALLEL_IDENTIFIER = 'identifier';

    /**
     * @var string
     */
    final public const PARALLEL_PORT = 'port';
}
