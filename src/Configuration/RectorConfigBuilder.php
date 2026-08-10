<?php

declare (strict_types=1);
namespace Rector\Configuration;

use Deprecated;
use PhpParser\NodeVisitor;
use Rector\Bridge\SetProviderCollector;
use Rector\Bridge\SetRectorsResolver;
use Rector\Caching\Contract\ValueObject\Storage\CacheStorageInterface;
use Rector\Composer\InstalledPackageResolver;
use Rector\Config\Level\CodeQualityLevel;
use Rector\Config\Level\CodingStyleLevel;
use Rector\Config\Level\DeadCodeLevel;
use Rector\Config\Level\TypeDeclarationDocblocksLevel;
use Rector\Config\Level\TypeDeclarationLevel;
use Rector\Config\RectorConfig;
use Rector\Config\RegisteredService;
use Rector\Configuration\Levels\LevelRulesResolver;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Console\Notifier;
use Rector\Contract\PhpParser\DecoratingNodeVisitorInterface;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Contract\Rector\RectorInterface;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Enum\Config\Defaults;
use Rector\Exception\Configuration\InvalidConfigurationException;
use Rector\Php\PhpVersionResolver\ComposerJsonPhpVersionResolver;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\Contract\SetProviderInterface;
use Rector\Set\Enum\SetGroup;
use Rector\Set\SetManager;
use Rector\Set\ValueObject\DowngradeLevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonyInternalSetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\Symfony\Set\TwigSetList;
use Rector\ValueObject\Configuration\LevelOverflow;
use Rector\ValueObject\PhpVersion;
use RectorPrefix202608\Symfony\Component\Finder\Finder;
use RectorPrefix202608\Webmozart\Assert\Assert;
/**
 * @api
 */
final class RectorConfigBuilder
{
    /**
     * @var int
     */
    private const MAX_LEVEL_GAP = 10;
    /**
     * A level method and the set that contains the very same rules,
     * so they are never enabled both at once
     *
     * @var array<string, array{string, string}> level method name => [set file path, set title]
     */
    private const LEVEL_METHOD_TO_SET = ['withTypeCoverageLevel' => [SetList::TYPE_DECLARATION, 'type declarations'], 'withTypeCoverageDocblockLevel' => [SetList::TYPE_DECLARATION_DOCBLOCKS, 'type declaration docblocks'], 'withDeadCodeLevel' => [SetList::DEAD_CODE, 'dead code'], 'withCodeQualityLevel' => [SetList::CODE_QUALITY, 'code quality'], 'withCodingStyleLevel' => [SetList::CODING_STYLE, 'coding style']];
    /**
     * The composer-based set of the extensions that rector-src does not require, so their set list class cannot be
     * imported here. Resolved at run-time; an extension that ships no such set falls back to its set group.
     *
     * @var array<SetGroup::*, string>
     */
    private const EXTENSION_COMPOSER_BASED_SET_LISTS = [SetGroup::LARAVEL => 'RectorLaravel\Set\LaravelSetList::COMPOSER_BASED', SetGroup::DRUPAL => 'DrupalRector\Set\DrupalSetList::COMPOSER_BASED'];
    /**
     * @var string[]
     */
    private array $paths = [];
    /**
     * @var string[]
     */
    private array $sets = [];
    /**
     * @var array<mixed>
     */
    private array $skip = [];
    /**
     * @var array<class-string<RectorInterface>>
     */
    private array $rules = [];
    /**
     * @var array<class-string<ConfigurableRectorInterface>, mixed[]>
     */
    private array $rulesWithConfigurations = [];
    /**
     * @var string[]
     */
    private array $fileExtensions = [];
    private ?string $cacheDirectory = null;
    private ?string $containerCacheDirectory = null;
    private ?bool $parallel = null;
    private int $parallelTimeoutSeconds = 120;
    private int $parallelMaxNumberOfProcess = Defaults::PARALLEL_MAX_NUMBER_OF_PROCESS;
    private int $parallelJobSize = 16;
    private bool $importNames = \false;
    private bool $importDocBlockNames = \false;
    private bool $importShortClasses = \true;
    private bool $removeUnusedImports = \false;
    private bool $noDiffs = \false;
    private ?string $memoryLimit = null;
    /**
     * @var string[]
     */
    private array $autoloadPaths = [];
    /**
     * @var string[]
     */
    private array $bootstrapFiles = [];
    private string $indentChar = ' ';
    private int $indentSize = 4;
    /**
     * @var string[]
     */
    private array $phpstanConfigs = [];
    /**
     * @var null|PhpVersion::*
     */
    private ?int $phpVersion = null;
    private ?string $symfonyContainerXmlFile = null;
    private ?string $symfonyContainerPhpFile = null;
    /**
     * To make sure a set and its level method are not duplicated,
     * as both contain same rules
     *
     * @var array<string, true> level method name => true
     */
    private array $usedLevelMethods = [];
    private ?bool $isFluentNewLine = null;
    private ?bool $isTreatClassesAsFinal = null;
    /**
     * @var string[]
     */
    private array $typeGuardedClasses = [];
    /**
     * @var RegisteredService[]
     */
    private array $registerServices = [];
    /**
     * @var array<SetGroup::*>
     */
    private array $setGroups = [];
    private ?bool $reportingRealPath = null;
    private ?bool $reportUnusedSkips = null;
    /**
     * @var string[]
     */
    private array $groupLoadedSets = [];
    private ?string $editorUrl = null;
    private ?bool $isWithPhpSetsUsed = null;
    private ?bool $isWithPhpLevelUsed = null;
    private ?int $pickedPhpSetsVersion = null;
    /**
     * @var array<class-string<SetProviderInterface>,bool>
     */
    private array $setProviders = [];
    /**
     * @var LevelOverflow[]
     */
    private array $levelOverflows = [];
    public function __invoke(RectorConfig $rectorConfig): void
    {
        if ($this->setGroups !== [] || $this->setProviders !== []) {
            $setProviderCollector = new SetProviderCollector(array_map(\Closure::fromCallable([$rectorConfig, 'make']), \array_keys($this->setProviders)));
            $setManager = new SetManager($setProviderCollector, new InstalledPackageResolver(getcwd()));
            $this->groupLoadedSets = $setManager->matchBySetGroups($this->setGroups);
            SimpleParameterProvider::addParameter(\Rector\Configuration\Option::COMPOSER_BASED_SETS, $this->groupLoadedSets);
        }
        // not to miss it by accident
        if ($this->isWithPhpSetsUsed === \true) {
            $this->sets[] = SetList::PHP_POLYFILLS;
        }
        if ($this->pickedPhpSetsVersion !== null) {
            SimpleParameterProvider::setParameter(\Rector\Configuration\Option::POLYFILL_CEILING_PHP_VERSION, $this->pickedPhpSetsVersion);
        }
        // merge sets together
        $this->sets = array_merge($this->sets, $this->groupLoadedSets);
        $uniqueSets = array_unique($this->sets);
        if ($this->isWithPhpLevelUsed && $this->isWithPhpSetsUsed) {
            throw new InvalidConfigurationException(sprintf('Your config uses "withPhp*()" and "withPhpLevel()" methods at the same time.%sPick one of them to avoid rule conflicts.', \PHP_EOL));
        }
        foreach (self::LEVEL_METHOD_TO_SET as $levelMethod => [$setFilePath, $setTitle]) {
            if (!isset($this->usedLevelMethods[$levelMethod])) {
                continue;
            }
            if (!in_array($setFilePath, $uniqueSets, \true)) {
                continue;
            }
            throw new InvalidConfigurationException(sprintf('Your config already enables %s set.%sRemove "->%s()" as it only duplicates it, or remove %s set.', $setTitle, \PHP_EOL, $levelMethod, $setTitle));
        }
        if ($uniqueSets !== []) {
            $rectorConfig->sets($uniqueSets);
        }
        // log rules from sets and compare them with explicit rules
        $setRegisteredRectorClasses = $rectorConfig->getMainRectorClasses();
        SimpleParameterProvider::addParameter(\Rector\Configuration\Option::SET_REGISTERED_RULES, $setRegisteredRectorClasses);
        if ($this->paths !== []) {
            $rectorConfig->paths($this->paths);
        }
        // must be in upper part, as these services might be used by rule registered bellow
        foreach ($this->registerServices as $registerService) {
            $rectorConfig->singleton($registerService->getClassName());
            if ($registerService->getAlias()) {
                $rectorConfig->alias($registerService->getClassName(), $registerService->getAlias());
            }
            if ($registerService->getTag()) {
                $rectorConfig->tag($registerService->getClassName(), $registerService->getTag());
            }
        }
        if ($this->skip !== []) {
            $rectorConfig->skip($this->skip);
        }
        if ($this->rules !== []) {
            $rectorConfig->rules($this->rules);
        }
        foreach ($this->rulesWithConfigurations as $rectorClass => $configurations) {
            foreach ($configurations as $configuration) {
                $rectorConfig->ruleWithConfiguration($rectorClass, $configuration);
            }
        }
        if ($this->fileExtensions !== []) {
            $rectorConfig->fileExtensions($this->fileExtensions);
        }
        if ($this->cacheDirectory !== null) {
            $rectorConfig->cacheDirectory($this->cacheDirectory);
        }
        if ($this->containerCacheDirectory !== null) {
            $rectorConfig->containerCacheDirectory($this->containerCacheDirectory);
        }
        if ($this->importNames || $this->importDocBlockNames) {
            $rectorConfig->importNames($this->importNames, $this->importDocBlockNames);
            $rectorConfig->importShortClasses($this->importShortClasses);
        }
        if ($this->removeUnusedImports) {
            $rectorConfig->removeUnusedImports($this->removeUnusedImports);
        }
        if ($this->noDiffs) {
            $rectorConfig->noDiffs();
        }
        if ($this->memoryLimit !== null) {
            $rectorConfig->memoryLimit($this->memoryLimit);
        }
        if ($this->autoloadPaths !== []) {
            $rectorConfig->autoloadPaths($this->autoloadPaths);
        }
        if ($this->bootstrapFiles !== []) {
            $rectorConfig->bootstrapFiles($this->bootstrapFiles);
        }
        if ($this->indentChar !== ' ' || $this->indentSize !== 4) {
            $rectorConfig->indent($this->indentChar, $this->indentSize);
        }
        if ($this->phpstanConfigs !== []) {
            $rectorConfig->phpstanConfigs($this->phpstanConfigs);
        }
        if ($this->phpVersion !== null) {
            $rectorConfig->phpVersion($this->phpVersion);
        }
        if ($this->parallel !== null) {
            if ($this->parallel) {
                $rectorConfig->parallel($this->parallelTimeoutSeconds, $this->parallelMaxNumberOfProcess, $this->parallelJobSize);
            } else {
                $rectorConfig->disableParallel();
            }
        }
        if ($this->symfonyContainerXmlFile !== null) {
            $rectorConfig->symfonyContainerXml($this->symfonyContainerXmlFile);
        }
        if ($this->symfonyContainerPhpFile !== null) {
            $rectorConfig->symfonyContainerPhp($this->symfonyContainerPhpFile);
        }
        if ($this->isFluentNewLine !== null) {
            $rectorConfig->newLineOnFluentCall($this->isFluentNewLine);
        }
        if ($this->typeGuardedClasses !== []) {
            $rectorConfig->typeGuardedClasses($this->typeGuardedClasses);
        }
        if ($this->isTreatClassesAsFinal !== null) {
            $rectorConfig->treatClassesAsFinal($this->isTreatClassesAsFinal);
        }
        if ($this->reportingRealPath !== null) {
            $rectorConfig->reportingRealPath($this->reportingRealPath);
        }
        if ($this->reportUnusedSkips !== null) {
            $rectorConfig->reportUnusedSkips($this->reportUnusedSkips);
        }
        if ($this->editorUrl !== null) {
            $rectorConfig->editorUrl($this->editorUrl);
        }
        if ($this->levelOverflows !== []) {
            $rectorConfig->setOverflowLevels($this->levelOverflows);
        }
    }
    /**
     * @param string[] $paths
     */
    public function withPaths(array $paths): self
    {
        $this->paths = $paths;
        return $this;
    }
    /**
     * @param array<mixed> $skip
     */
    public function withSkip(array $skip): self
    {
        $this->skip = array_merge($this->skip, $skip);
        return $this;
    }
    public function withSkipPath(string $skipPath): self
    {
        if (strpos($skipPath, '*') === \false) {
            Assert::fileExists($skipPath);
        }
        return $this->withSkip([$skipPath]);
    }
    /**
     * Include PHP files from the root directory (including hidden ones),
     * typically ecs.php, rector.php, .php-cs-fixer.dist.php etc.
     */
    public function withRootFiles(): self
    {
        $rootPhpFilesFinder = (new Finder())->files()->in(getcwd())->depth(0)->ignoreDotFiles(\false)->ignoreVCSIgnored(\true)->name('*.php')->name('.*.php')->notName('.phpstorm.meta.php');
        foreach ($rootPhpFilesFinder as $rootPhpFileFinder) {
            $path = $rootPhpFileFinder->getRealPath();
            $this->paths[] = $path;
        }
        return $this;
    }
    /**
     * @param string[] $sets
     */
    public function withSets(array $sets): self
    {
        $this->sets = array_merge($this->sets, $sets);
        return $this;
    }
    /**
     * Upgrade your annotations to attributes
     *
     * @param bool $symfonyRoute Deprecated, included in $symfony
     * @param bool $symfonyValidator Deprecated, included in $symfony
     */
    public function withAttributesSets(bool $symfony = \false, bool $doctrine = \false, bool $mongoDb = \false, bool $gedmo = \false, bool $phpunit = \false, bool $fosRest = \false, bool $jms = \false, bool $sensiolabs = \false, bool $behat = \false, bool $all = \false, bool $symfonyRoute = \false, bool $symfonyValidator = \false): self
    {
        // if nothing is passed, enable all as convention in other method
        if (func_get_args() === []) {
            $all = \true;
        }
        if ($symfony || $all) {
            $this->sets[] = SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES;
        }
        // both are part of $symfony set, no longer applied on their own
        if ($symfonyRoute) {
            SimpleParameterProvider::addParameter(\Rector\Configuration\Option::DEPRECATED_ATTRIBUTES_SETS_ARGS, 'symfonyRoute');
        }
        if ($symfonyValidator) {
            SimpleParameterProvider::addParameter(\Rector\Configuration\Option::DEPRECATED_ATTRIBUTES_SETS_ARGS, 'symfonyValidator');
        }
        if ($doctrine || $all) {
            $this->sets[] = DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES;
        }
        if ($mongoDb || $all) {
            $this->sets[] = DoctrineSetList::MONGODB_ANNOTATIONS_TO_ATTRIBUTES;
        }
        if ($gedmo || $all) {
            $this->sets[] = DoctrineSetList::GEDMO_ANNOTATIONS_TO_ATTRIBUTES;
        }
        if ($fosRest || $all) {
            $this->sets[] = SymfonyInternalSetList::FOS_REST_ANNOTATIONS_TO_ATTRIBUTES;
        }
        if ($jms || $all) {
            $this->sets[] = SymfonyInternalSetList::JMS_ANNOTATIONS_TO_ATTRIBUTES;
        }
        if ($sensiolabs || $all) {
            $this->sets[] = SymfonyInternalSetList::SENSIOLABS_ANNOTATIONS_TO_ATTRIBUTES;
        }
        if ($phpunit || $all) {
            $this->sets[] = PHPUnitSetList::ANNOTATIONS_TO_ATTRIBUTES;
        }
        if ($behat || $all) {
            $this->sets[] = SetList::BEHAT_ANNOTATIONS_TO_ATTRIBUTES;
        }
        return $this;
    }
    /**
     * What PHP sets should be applied? By default the same version
     * as composer.json has is used
     */
    public function withPhpSets(
        bool $php83 = \false,
        bool $php82 = \false,
        bool $php81 = \false,
        bool $php80 = \false,
        bool $php74 = \false,
        bool $php73 = \false,
        bool $php72 = \false,
        bool $php71 = \false,
        bool $php70 = \false,
        bool $php56 = \false,
        bool $php55 = \false,
        bool $php54 = \false,
        bool $php53 = \false,
        // place on later as BC break when used in php 7.x without named arg
        bool $php84 = \false,
        bool $php85 = \false,
        bool $php86 = \false
    ): self
    {
        if ($this->isWithPhpSetsUsed === \true) {
            throw new InvalidConfigurationException(sprintf('Method "%s()" can be called only once. It always includes all previous sets UP TO the defined version.%sThe best practise is to call it once with no argument. That way it will pick up PHP version from composer.json and your project will always stay up to date.', __METHOD__, \PHP_EOL));
        }
        $this->isWithPhpSetsUsed = \true;
        $pickedPhpVersions = array_keys(array_filter([PhpVersion::PHP_53 => $php53, PhpVersion::PHP_54 => $php54, PhpVersion::PHP_55 => $php55, PhpVersion::PHP_56 => $php56, PhpVersion::PHP_70 => $php70, PhpVersion::PHP_71 => $php71, PhpVersion::PHP_72 => $php72, PhpVersion::PHP_73 => $php73, PhpVersion::PHP_74 => $php74, PhpVersion::PHP_80 => $php80, PhpVersion::PHP_81 => $php81, PhpVersion::PHP_82 => $php82, PhpVersion::PHP_83 => $php83, PhpVersion::PHP_84 => $php84, PhpVersion::PHP_85 => $php85, PhpVersion::PHP_86 => $php86]));
        if ($pickedPhpVersions !== []) {
            Notifier::errorWithPhpSetsNotSuitableForPHP74AndLower();
        }
        if (count($pickedPhpVersions) > 1) {
            throw new InvalidConfigurationException(sprintf('Pick only one version target in "withPhpSets()". All rules up to this version will be used.%sTo use your composer.json PHP version, keep arguments empty.', \PHP_EOL));
        }
        // no version picked, resolve it from the project composer.json
        if ($pickedPhpVersions === []) {
            return $this->addPhpLevelSets(ComposerJsonPhpVersionResolver::resolveFromCwdOrFail());
        }
        // explicitly picked version is a ceiling, even for polyfilled rules
        $this->pickedPhpSetsVersion = $pickedPhpVersions[0];
        return $this->addPhpLevelSets($pickedPhpVersions[0]);
    }
    public function withPhp53Sets(): self
    {
        return $this->reportDeprecatedPhpSetsMethod(__FUNCTION__);
    }
    public function withPhp54Sets(): self
    {
        return $this->reportDeprecatedPhpSetsMethod(__FUNCTION__);
    }
    public function withPhp55Sets(): self
    {
        return $this->reportDeprecatedPhpSetsMethod(__FUNCTION__);
    }
    public function withPhp56Sets(): self
    {
        return $this->reportDeprecatedPhpSetsMethod(__FUNCTION__);
    }
    public function withPhp70Sets(): self
    {
        return $this->reportDeprecatedPhpSetsMethod(__FUNCTION__);
    }
    public function withPhp71Sets(): self
    {
        return $this->reportDeprecatedPhpSetsMethod(__FUNCTION__);
    }
    public function withPhp72Sets(): self
    {
        return $this->reportDeprecatedPhpSetsMethod(__FUNCTION__);
    }
    public function withPhp73Sets(): self
    {
        return $this->reportDeprecatedPhpSetsMethod(__FUNCTION__);
    }
    public function withPhp74Sets(): self
    {
        return $this->reportDeprecatedPhpSetsMethod(__FUNCTION__);
    }
    // there is no withPhp80Sets() and above,
    // as we already use PHP 8.0 and should go with withPhpSets() instead
    public function withPreparedSets(bool $deadCode = \false, bool $codeQuality = \false, bool $codingStyle = \false, bool $typeDeclarations = \false, bool $typeDeclarationDocblocks = \false, bool $privatization = \false, bool $naming = \false, bool $namedArgs = \false, bool $instanceOf = \false, bool $if = \false, bool $earlyReturn = \false, bool $carbon = \false, bool $rectorPreset = \false, bool $phpunitCodeQuality = \false, bool $phpunitNarrowAsserts = \false, bool $phpunitMockToStub = \false, bool $doctrineCodeQuality = \false, bool $symfonyCodeQuality = \false, bool $symfonyConfigs = \false): self
    {
        Notifier::notifyNotSuitableMethodForPHP74(__METHOD__);
        $setMap = [SetList::DEAD_CODE => $deadCode, SetList::CODE_QUALITY => $codeQuality, SetList::CODING_STYLE => $codingStyle, SetList::TYPE_DECLARATION => $typeDeclarations, SetList::TYPE_DECLARATION_DOCBLOCKS => $typeDeclarationDocblocks, SetList::PRIVATIZATION => $privatization, SetList::NAMING => $naming, SetList::NAMED_ARGS => $namedArgs, SetList::INSTANCEOF => $instanceOf, SetList::IF => $if, SetList::EARLY_RETURN => $earlyReturn, SetList::CARBON => $carbon, SetList::RECTOR_PRESET => $rectorPreset, PHPUnitSetList::PHPUNIT_CODE_QUALITY => $phpunitCodeQuality, PHPUnitSetList::PHPUNIT_NARROW_ASSERTS => $phpunitNarrowAsserts, PHPUnitSetList::PHPUNIT_MOCK_TO_STUB => $phpunitMockToStub, DoctrineSetList::DOCTRINE_CODE_QUALITY => $doctrineCodeQuality, SymfonySetList::SYMFONY_CODE_QUALITY => $symfonyCodeQuality, SymfonySetList::CONFIGS => $symfonyConfigs];
        foreach ($setMap as $setPath => $isEnabled) {
            if ($isEnabled) {
                $this->sets[] = $setPath;
            }
        }
        return $this;
    }
    public function withComposerBased(bool $twig = \false, bool $doctrine = \false, bool $phpunit = \false, bool $symfony = \false, bool $netteUtils = \false, bool $laravel = \false, bool $drupal = \false): self
    {
        $setMap = [SetGroup::LARAVEL => $laravel, SetGroup::DRUPAL => $drupal];
        foreach ($setMap as $setGroup => $isEnabled) {
            if (!$isEnabled) {
                continue;
            }
            $setListConstant = self::EXTENSION_COMPOSER_BASED_SET_LISTS[$setGroup];
            if (defined($setListConstant)) {
                $setFilePath = constant($setListConstant);
                Assert::string($setFilePath);
                // single set, as every rule inside is bound to the installed package version on its own
                $this->sets[] = $setFilePath;
                continue;
            }
            // @deprecated fallback for extensions that still describe their sets as objects,
            // instead of bonding the rules themselves
            $this->setGroups[] = $setGroup;
        }
        if ($phpunit) {
            // single set, as every rule inside is bound to the installed PHPUnit version on its own
            $this->sets[] = PHPUnitSetList::COMPOSER_BASED;
        }
        if ($doctrine) {
            $this->sets[] = DoctrineSetList::COMPOSER_BASED;
        }
        if ($twig) {
            $this->sets[] = TwigSetList::COMPOSER_BASED;
        }
        if ($symfony) {
            // single set, as every rule inside is bound to the installed Symfony package version on its own
            $this->sets[] = SymfonySetList::COMPOSER_BASED;
        }
        // deprecated, no longer applied - it only added named args to 2 methods of a single package
        if ($netteUtils) {
            SimpleParameterProvider::addParameter(\Rector\Configuration\Option::DEPRECATED_COMPOSER_BASED_ARGS, 'netteUtils');
        }
        return $this;
    }
    /**
     * @param array<class-string<RectorInterface>> $rules
     */
    public function withRules(array $rules): self
    {
        $this->rules = array_merge($this->rules, $rules);
        if (SimpleParameterProvider::provideBoolParameter(\Rector\Configuration\Option::IS_RECTORCONFIG_BUILDER_RECREATED, \false) === \false) {
            // log all explicitly registered rules on root rector.php
            // we only check the non-configurable rules, as the configurable ones might override them
            $nonConfigurableRules = array_filter($rules, fn(string $rule): bool => !is_a($rule, ConfigurableRectorInterface::class, \true));
            SimpleParameterProvider::addParameter(\Rector\Configuration\Option::ROOT_STANDALONE_REGISTERED_RULES, $nonConfigurableRules);
        }
        return $this;
    }
    /**
     * @param string[] $fileExtensions
     */
    public function withFileExtensions(array $fileExtensions): self
    {
        $this->fileExtensions = $fileExtensions;
        return $this;
    }
    /**
     * The $cacheClass argument is deprecated and ignored. Cache storage is selected automatically:
     * file cache locally, in-memory cache in CI.
     *
     * @param class-string<CacheStorageInterface>|null $cacheClass
     */
    public function withCache(?string $cacheDirectory = null, ?string $cacheClass = null, ?string $containerCacheDirectory = null): self
    {
        $this->cacheDirectory = $cacheDirectory;
        $this->containerCacheDirectory = $containerCacheDirectory;
        return $this;
    }
    /**
     * @param class-string $cacheMetaExtensionClass
     */
    public function withCacheMetaExtension(string $cacheMetaExtensionClass): self
    {
        SimpleParameterProvider::addParameter(\Rector\Configuration\Option::CACHE_META_EXTENSIONS, $cacheMetaExtensionClass);
        return $this;
    }
    /**
     * @param class-string<ConfigurableRectorInterface> $rectorClass
     * @param mixed[] $configuration
     */
    public function withConfiguredRule(string $rectorClass, array $configuration): self
    {
        $this->rulesWithConfigurations[$rectorClass][] = $configuration;
        return $this;
    }
    public function withParallel(?int $timeoutSeconds = null, ?int $maxNumberOfProcess = null, ?int $jobSize = null): self
    {
        $this->parallel = \true;
        if (is_int($timeoutSeconds)) {
            $this->parallelTimeoutSeconds = $timeoutSeconds;
        }
        if (is_int($maxNumberOfProcess)) {
            $this->parallelMaxNumberOfProcess = $maxNumberOfProcess;
        }
        if (is_int($jobSize)) {
            $this->parallelJobSize = $jobSize;
        }
        return $this;
    }
    public function withoutParallel(): self
    {
        $this->parallel = \false;
        return $this;
    }
    public function withImportNames(bool $importNames = \true, bool $importDocBlockNames = \true, bool $importShortClasses = \true, bool $removeUnusedImports = \true): self
    {
        $this->importNames = $importNames;
        $this->importDocBlockNames = $importDocBlockNames;
        $this->importShortClasses = $importShortClasses;
        $this->removeUnusedImports = $removeUnusedImports;
        return $this;
    }
    public function withNoDiffs(): self
    {
        $this->noDiffs = \true;
        return $this;
    }
    public function withMemoryLimit(string $memoryLimit): self
    {
        $this->memoryLimit = $memoryLimit;
        return $this;
    }
    public function withIndent(string $indentChar = ' ', int $indentSize = 4): self
    {
        $this->indentChar = $indentChar;
        $this->indentSize = $indentSize;
        return $this;
    }
    /**
     * @param string[] $autoloadPaths
     */
    public function withAutoloadPaths(array $autoloadPaths): self
    {
        $this->autoloadPaths = $autoloadPaths;
        return $this;
    }
    /**
     * @param string[] $bootstrapFiles
     */
    public function withBootstrapFiles(array $bootstrapFiles): self
    {
        $this->bootstrapFiles = $bootstrapFiles;
        return $this;
    }
    /**
     * @param string[] $phpstanConfigs
     */
    public function withPHPStanConfigs(array $phpstanConfigs): self
    {
        $this->phpstanConfigs = $phpstanConfigs;
        return $this;
    }
    /**
     * @param PhpVersion::* $phpVersion
     */
    public function withPhpVersion(int $phpVersion): self
    {
        $this->phpVersion = $phpVersion;
        return $this;
    }
    public function withSymfonyContainerXml(string $symfonyContainerXmlFile): self
    {
        if (substr_compare($symfonyContainerXmlFile, '.xml', -strlen('.xml')) !== 0) {
            throw new InvalidConfigurationException(sprintf('Provided dumped Symfony container must have "xml" suffix. "%s" given', $symfonyContainerXmlFile));
        }
        $this->symfonyContainerXmlFile = $symfonyContainerXmlFile;
        return $this;
    }
    public function withSymfonyContainerPhp(string $symfonyContainerPhpFile): self
    {
        if (substr_compare($symfonyContainerPhpFile, '.php', -strlen('.php')) !== 0) {
            throw new InvalidConfigurationException(sprintf('Provided dumped Symfony container must have "php" suffix. "%s" given', $symfonyContainerPhpFile));
        }
        $this->symfonyContainerPhpFile = $symfonyContainerPhpFile;
        return $this;
    }
    /**
     * Raise your type coverage from the safest type rules
     * to more affecting ones, one level at a time
     */
    public function withTypeCoverageLevel(int $level): self
    {
        return $this->addLevelRules('withTypeCoverageLevel', $level, TypeDeclarationLevel::RULES, 'typeDeclarations', 'TYPE_DECLARATION');
    }
    /**
     * Raise your type coverage docblock from the safest type rules
     * to more affecting ones, one level at a time
     */
    public function withTypeCoverageDocblockLevel(int $level): self
    {
        return $this->addLevelRules('withTypeCoverageDocblockLevel', $level, TypeDeclarationDocblocksLevel::RULES, 'typeDeclarationDocblocks', 'TYPE_DECLARATION_DOCBLOCKS');
    }
    /**
     * Raise your dead-code coverage from the safest rules
     * to more affecting ones, one level at a time
     */
    public function withDeadCodeLevel(int $level): self
    {
        return $this->addLevelRules('withDeadCodeLevel', $level, DeadCodeLevel::RULES, 'deadCode', 'DEAD_CODE');
    }
    /**
     * Raise your PHP level from, one level at a time
     */
    public function withPhpLevel(int $level): self
    {
        Assert::natural($level);
        $this->isWithPhpLevelUsed = \true;
        $phpVersion = ComposerJsonPhpVersionResolver::resolveFromCwdOrFail();
        $setRectorsResolver = new SetRectorsResolver();
        $setFilePaths = \Rector\Configuration\PhpLevelSetResolver::resolveFromPhpVersion($phpVersion);
        $rectorRulesWithConfiguration = $setRectorsResolver->resolveFromFilePathsIncludingConfiguration($setFilePaths);
        foreach ($rectorRulesWithConfiguration as $position => $rectorRuleWithConfiguration) {
            // add rules until level is reached
            if ($position > $level) {
                break;
            }
            if (is_string($rectorRuleWithConfiguration)) {
                $this->rules[] = $rectorRuleWithConfiguration;
            } elseif (is_array($rectorRuleWithConfiguration)) {
                foreach ($rectorRuleWithConfiguration as $rectorRule => $rectorRuleConfiguration) {
                    /** @var class-string<ConfigurableRectorInterface> $rectorRule */
                    $this->withConfiguredRule($rectorRule, $rectorRuleConfiguration);
                }
            }
        }
        return $this;
    }
    /**
     * Raise your code quality from the safest rules
     * to more affecting ones, one level at a time
     */
    public function withCodeQualityLevel(int $level): self
    {
        $this->addLevelRules('withCodeQualityLevel', $level, CodeQualityLevel::RULES, 'codeQuality', 'CODE_QUALITY');
        foreach (CodeQualityLevel::RULES_WITH_CONFIGURATION as $rectorClass => $configuration) {
            $this->rulesWithConfigurations[$rectorClass][] = $configuration;
        }
        return $this;
    }
    /**
     * Raise your coding style from the safest rules
     * to more affecting ones, one level at a time
     */
    public function withCodingStyleLevel(int $level): self
    {
        $this->addLevelRules('withCodingStyleLevel', $level, CodingStyleLevel::RULES, 'codingStyle', 'CODING_STYLE');
        foreach (CodingStyleLevel::RULES_WITH_CONFIGURATION as $rectorClass => $configuration) {
            $this->rulesWithConfigurations[$rectorClass][] = $configuration;
        }
        return $this;
    }
    public function withFluentCallNewLine(bool $isFluentNewLine = \true): self
    {
        $this->isFluentNewLine = $isFluentNewLine;
        return $this;
    }
    public function withTreatClassesAsFinal(bool $isTreatClassesAsFinal = \true): self
    {
        $this->isTreatClassesAsFinal = $isTreatClassesAsFinal;
        return $this;
    }
    /**
     * Guard the listed classes and their non-final descendants against method signature changes
     * that would break child classes - e.g. adding a return type or a param type.
     *
     * @param string[] $typeGuardedClasses
     */
    public function withTypeGuardedClasses(array $typeGuardedClasses): self
    {
        $this->typeGuardedClasses = $typeGuardedClasses;
        return $this;
    }
    public function registerService(string $className, ?string $alias = null, ?string $tag = null): self
    {
        $this->registerServices[] = new RegisteredService($className, $alias, $tag);
        return $this;
    }
    /**
     * DX helper
     * @see https://getrector.com/documentation/creating-a-node-visitor
     * @param class-string $decoratingNodeVisitorClass
     */
    public function registerDecoratingNodeVisitor(string $decoratingNodeVisitorClass): self
    {
        Assert::isAOf($decoratingNodeVisitorClass, NodeVisitor::class);
        $this->registerServices[] = new RegisteredService($decoratingNodeVisitorClass, null, DecoratingNodeVisitorInterface::class);
        return $this;
    }
    public function withDowngradeSets(bool $php84 = \false, bool $php83 = \false, bool $php82 = \false, bool $php81 = \false, bool $php80 = \false, bool $php74 = \false, bool $php73 = \false, bool $php72 = \false, bool $php71 = \false): self
    {
        $pickedDowngradeSets = array_keys(array_filter([DowngradeLevelSetList::DOWN_TO_PHP_84 => $php84, DowngradeLevelSetList::DOWN_TO_PHP_83 => $php83, DowngradeLevelSetList::DOWN_TO_PHP_82 => $php82, DowngradeLevelSetList::DOWN_TO_PHP_81 => $php81, DowngradeLevelSetList::DOWN_TO_PHP_80 => $php80, DowngradeLevelSetList::DOWN_TO_PHP_74 => $php74, DowngradeLevelSetList::DOWN_TO_PHP_73 => $php73, DowngradeLevelSetList::DOWN_TO_PHP_72 => $php72, DowngradeLevelSetList::DOWN_TO_PHP_71 => $php71]));
        if (count($pickedDowngradeSets) !== 1) {
            throw new InvalidConfigurationException('Pick only one PHP version target in "withDowngradeSets()". All rules down to this version will be used.');
        }
        $this->sets[] = $pickedDowngradeSets[0];
        return $this;
    }
    public function withRealPathReporting(bool $absolutePath = \true): self
    {
        $this->reportingRealPath = $absolutePath;
        return $this;
    }
    /**
     * Report skips configured via withSkip() that never matched anything during the run,
     * so they can be safely removed.
     */
    public function reportUnusedSkips(bool $report = \true): self
    {
        $this->reportUnusedSkips = $report;
        return $this;
    }
    public function withEditorUrl(string $editorUrl): self
    {
        $this->editorUrl = $editorUrl;
        return $this;
    }
    /**
     * @param class-string<SetProviderInterface> ...$setProviders
     */
    public function withSetProviders(string ...$setProviders): self
    {
        foreach ($setProviders as $setProvider) {
            if (\array_key_exists($setProvider, $this->setProviders)) {
                continue;
            }
            if (!is_a($setProvider, SetProviderInterface::class, \true)) {
                throw new InvalidConfigurationException(sprintf('Set provider "%s" must implement "%s"', $setProvider, SetProviderInterface::class));
            }
            $this->setProviders[$setProvider] = \true;
        }
        return $this;
    }
    /**
     * @param PhpVersion::* $phpVersion
     */
    private function addPhpLevelSets(int $phpVersion): self
    {
        $this->isWithPhpSetsUsed = \true;
        $this->sets = array_merge($this->sets, \Rector\Configuration\PhpLevelSetResolver::resolveFromPhpVersion($phpVersion));
        return $this;
    }
    private function reportDeprecatedPhpSetsMethod(string $methodName): self
    {
        SimpleParameterProvider::addParameter(\Rector\Configuration\Option::DEPRECATED_PHP_SETS_METHODS, $methodName);
        return $this;
    }
    /**
     * @param array<class-string<RectorInterface>> $availableRules
     */
    private function addLevelRules(string $levelMethod, int $level, array $availableRules, string $suggestedRuleset, string $suggestedSetListConstant): self
    {
        Assert::natural($level);
        $this->usedLevelMethods[$levelMethod] = \true;
        $levelRules = LevelRulesResolver::resolve($level, $availableRules, $levelMethod);
        // too high
        $levelRulesCount = count($levelRules);
        if ($levelRulesCount + self::MAX_LEVEL_GAP < $level) {
            $this->levelOverflows[] = new LevelOverflow($levelMethod, $level, $levelRulesCount, $suggestedRuleset, $suggestedSetListConstant);
        }
        $this->rules = array_merge($this->rules, $levelRules);
        return $this;
    }
}
