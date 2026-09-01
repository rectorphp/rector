<?php

declare (strict_types=1);
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
    public const AUTOLOAD_FILE = 'autoload-file';
    /**
     * @internal Use
     * @var string @see \Rector\Config\RectorConfig::bootstrapFiles() instead
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
     * @internal Use
     * @var string @see \Rector\Config\RectorConfig::phpVersion() instead
     * @var string
     */
    public const PHP_VERSION_FEATURES = 'php_version_features';
    /**
     * @internal Use
     * @var string @see \Rector\Config\RectorConfig::importNames() instead
     * @var string
     */
    public const AUTO_IMPORT_NAMES = 'auto_import_names';
    /**
     * @internal Use
     * @var string @see \Rector\Config\RectorConfig::polyfillPackages() instead
     * @var string
     */
    public const POLYFILL_PACKAGES = 'polyfill_packages';
    /**
     * PHP version explicitly picked in withPhpSets(), e.g. withPhpSets(php82: true).
     * Polyfilled rules above this version are skipped, as the version is an intended ceiling.
     *
     * @internal
     * @var string
     */
    public const POLYFILL_CEILING_PHP_VERSION = 'polyfill_ceiling_php_version';
    /**
     * @internal Use
     * @var string @see \Rector\Config\RectorConfig::importNames() instead
     * @var string
     */
    public const AUTO_IMPORT_DOC_BLOCK_NAMES = 'auto_import_doc_block_names';
    /**
     * @internal Use
     * @var string @see \Rector\Config\RectorConfig::importShortClasses() instead
     * @var string
     */
    public const IMPORT_SHORT_CLASSES = 'import_short_classes';
    /**
     * @internal Use
     * @var string @see \Rector\Config\RectorConfig::symfonyContainerXml() instead
     * @var string
     */
    public const SYMFONY_CONTAINER_XML_PATH_PARAMETER = 'symfony_container_xml_path';
    /**
     * @internal Use
     * @var string @see \Rector\Config\RectorConfig::symfonyContainerPhp()
     * @var string
     */
    public const SYMFONY_CONTAINER_PHP_PATH_PARAMETER = 'symfony_container_php_path';
    /**
     * @internal Use
     * @var string @see \Rector\Config\RectorConfig::newLineOnFluentCall()
     * @var string
     */
    public const NEW_LINE_ON_FLUENT_CALL = 'new_line_on_fluent_call';
    /**
     * @var string
     */
    public const CLEAR_CACHE = 'clear-cache';
    /**
     * @var string
     */
    public const ONLY = 'only';
    /**
     * @internal Use
     * @var string @see \Rector\Config\RectorConfig::parallel() instead
     * @var string
     */
    public const PARALLEL = 'parallel';
    /**
     * @internal Use
     * @var string @see \Rector\Config\RectorConfig::paths() instead
     * @var string
     */
    public const PATHS = 'paths';
    /**
     * @internal Use
     * @var string @see \Rector\Config\RectorConfig::autoloadPaths() instead
     * @var string
     */
    public const AUTOLOAD_PATHS = 'autoload_paths';
    /**
     * @internal Use
     * @var string @see \Rector\Config\RectorConfig::skip() instead
     * @var string
     */
    public const SKIP = 'skip';
    /**
     * @internal Use
     * @var string @see \Rector\Config\RectorConfig::reportUnusedSkips() instead
     * @var string
     */
    public const REPORT_UNUSED_SKIPS = 'report_unused_skips';
    /**
     * True when the run is narrowed on the command line - via paths argument, "--only" or
     * "--only-suffix". Unused skip reporting is then disabled, as skips outside the narrowed scope
     * look falsely unused and would produce many false positives.
     *
     * @internal
     * @var string
     */
    public const IS_RUN_NARROWED = 'is_run_narrowed';
    /**
     * True when the unchanged-files cache dropped at least one file from the run, so rules only ran
     * on the changed subset. Unused skip reporting is then disabled, as skips on cached files never
     * get a chance to match and would all look falsely unused.
     *
     * @internal
     * @var string
     */
    public const IS_CACHED_RUN = 'is_cached_run';
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
    public const RULES_SUMMARY = 'rules-summary';
    /**
     * @var string
     */
    public const CONFIG = 'config';
    /**
     * @internal Use
     * @var string @see \Rector\Config\RectorConfig::phpstanConfig() instead
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
     * @internal Use
     * @var string @see \Rector\Config\RectorConfig::parallel() instead with pass int $jobSize parameter
     * @var string
     */
    public const PARALLEL_JOB_SIZE = 'parallel-job-size';
    /**
     * @internal Use
     * @var string @see \Rector\Config\RectorConfig::parallel() instead with pass int $maxNumberOfProcess parameter
     * @var string
     */
    public const PARALLEL_MAX_NUMBER_OF_PROCESSES = 'parallel-max-number-of-processes';
    /**
     * @internal Use
     * @var string @see \Rector\Config\RectorConfig::parallel() instead with pass int $seconds parameter
     * @var string
     */
    public const PARALLEL_JOB_TIMEOUT_IN_SECONDS = 'parallel-job-timeout-in-seconds';
    /**
     * @var string
     */
    public const MEMORY_LIMIT = 'memory-limit';
    /**
     * @internal Use
     * @var string @see \Rector\Config\RectorConfig::indent() method
     * @var string
     */
    public const INDENT_CHAR = 'indent-char';
    /**
     * @internal Use
     * @var string @see \Rector\Config\RectorConfig::indent() method
     * @var string
     */
    public const INDENT_SIZE = 'indent-size';
    /**
     * @internal Use
     * @var string @see \Rector\Config\RectorConfig::removeUnusedImports() method
     * @var string
     */
    public const REMOVE_UNUSED_IMPORTS = 'remove-unused-imports';
    /**
     * @internal Use
     * @var string @see \Rector\Config\RectorConfig::containerCacheDirectory() method
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
     * @internal For cache invalidation when a configurable rule value changes
     * @var string
     */
    public const RULE_CONFIGURATIONS = 'rule_configurations';
    /**
     * @internal For verify RectorConfigBuilder instance recreated
     * @var string
     */
    public const IS_RECTORCONFIG_BUILDER_RECREATED = 'is_rectorconfig_builder_recreated';
    /**
     * @internal For verify skipped rules exists in registered rules
     * @var string
     */
    public const SKIPPED_RECTOR_RULES = 'skipped_rector_rules';
    /**
     * @internal For reporting skipped classes that are not Rector rules
     * @var string
     */
    public const SKIPPED_NON_RECTOR_CLASSES = 'skipped_non_rector_classes';
    /**
     * @internal For reporting deprecated cache meta extensions
     * @var string
     */
    public const CACHE_META_EXTENSIONS = 'cache_meta_extensions';
    /**
     * @internal For reporting deprecated withPhp53Sets() ... withPhp74Sets() methods
     * @var string
     */
    public const DEPRECATED_PHP_SETS_METHODS = 'deprecated_php_sets_methods';
    /**
     * @internal For reporting deprecated withAttributesSets() arguments
     * @var string
     */
    public const DEPRECATED_ATTRIBUTES_SETS_ARGS = 'deprecated_attributes_sets_args';
    /**
     * @internal For reporting deprecated withComposerBased() arguments
     * @var string
     */
    public const DEPRECATED_COMPOSER_BASED_ARGS = 'deprecated_composer_based_args';
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
    /**
     * @internal Use
     * @var string @see \Rector\Config\RectorConfig::treatClassesAsFinal() method
     * @var string
     */
    public const TREAT_CLASSES_AS_FINAL = 'treat_classes_as_final';
    /**
     * @internal To report rule configuration bound to an installed package version
     * @see \Rector\Config\RectorConfig::ruleWithConfigurationComposerVersionBound()
     * @var string
     */
    public const COMPOSER_BOUND_RULE_CONFIGURATIONS = 'composer_bound_rule_configurations';
    /**
     * Run only rules bound to an installed composer package version
     * @var string
     */
    public const COMPOSER_BASED = 'composer-based';
    /**
     * Run only rules bound to a minimal PHP version
     * @var string
     */
    public const PHP = 'php';
    /**
     * @internal To filter files by specific suffix
     * @var string
     */
    public const ONLY_SUFFIX = 'only-suffix';
    /**
     * @internal To keep only files matching all given patterns
     * @var string
     */
    public const FILTER = 'filter';
    /**
     * @internal To report overflow levels in ->with*Level() methods
     * @var string
     */
    public const LEVEL_OVERFLOWS = 'level_overflows';
    /**
     * @internal To avoid registering rules via ->withRules(), that are already loaded in sets,
     * and keep rector.php clean
     * @var string
     */
    public const ROOT_STANDALONE_REGISTERED_RULES = 'root_standalone_registered_rules';
    /**
     * @internal The other half of ROOT_STANDALONE_REGISTERED_RULES to compare
     * @var string
     */
    public const SET_REGISTERED_RULES = 'set_registered_rules';
    /**
     * @internal to allow process file without extension if explicitly registered
     * @var string
     */
    public const FILES_WITHOUT_EXTENSION = 'files_without_extension';
    /**
     * @internal Use
     * @var string @see \Rector\Config\RectorConfig::typeGuardedClasses() instead
     * @var string
     */
    public const TYPE_GUARDED_CLASSES = 'type_guarded_classes';
}
