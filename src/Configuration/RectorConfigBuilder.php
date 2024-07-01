<?php

declare (strict_types=1);
namespace Rector\Configuration;

use RectorPrefix202407\Nette\Utils\FileSystem;
use Rector\Bridge\SetProviderCollector;
use Rector\Caching\Contract\ValueObject\Storage\CacheStorageInterface;
use Rector\Config\Level\CodeQualityLevel;
use Rector\Config\Level\DeadCodeLevel;
use Rector\Config\Level\TypeDeclarationLevel;
use Rector\Config\RectorConfig;
use Rector\Config\RegisteredService;
use Rector\Configuration\Levels\LevelRulesResolver;
use Rector\Console\Notifier;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Contract\Rector\RectorInterface;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Exception\Configuration\InvalidConfigurationException;
use Rector\Php\PhpVersionResolver\ProjectComposerJsonPhpVersionResolver;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\Enum\SetGroup;
use Rector\Set\SetManager;
use Rector\Set\ValueObject\DowngradeLevelSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\FOSRestSetList;
use Rector\Symfony\Set\JMSSetList;
use Rector\Symfony\Set\SensiolabsSetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\ValueObject\PhpVersion;
use RectorPrefix202407\Symfony\Component\Finder\Finder;
use RectorPrefix202407\Webmozart\Assert\Assert;
/**
 * @api
 */
final class RectorConfigBuilder
{
    /**
     * @var string[]
     */
    private $paths = [];
    /**
     * @var string[]
     */
    private $sets = [];
    /**
     * @var array<mixed>
     */
    private $skip = [];
    /**
     * @var array<class-string<RectorInterface>>
     */
    private $rules = [];
    /**
     * @var array<class-string<ConfigurableRectorInterface>, mixed[]>
     */
    private $rulesWithConfigurations = [];
    /**
     * @var string[]
     */
    private $fileExtensions = [];
    /**
     * @var null|class-string<CacheStorageInterface>
     */
    private $cacheClass;
    /**
     * @var string|null
     */
    private $cacheDirectory;
    /**
     * @var string|null
     */
    private $containerCacheDirectory;
    /**
     * @var bool|null
     */
    private $parallel;
    /**
     * @var int
     */
    private $parallelTimeoutSeconds = 120;
    /**
     * @var int
     */
    private $parallelMaxNumberOfProcess = 16;
    /**
     * @var int
     */
    private $parallelJobSize = 16;
    /**
     * @var bool
     */
    private $importNames = \false;
    /**
     * @var bool
     */
    private $importDocBlockNames = \false;
    /**
     * @var bool
     */
    private $importShortClasses = \true;
    /**
     * @var bool
     */
    private $removeUnusedImports = \false;
    /**
     * @var bool
     */
    private $noDiffs = \false;
    /**
     * @var string|null
     */
    private $memoryLimit;
    /**
     * @var string[]
     */
    private $autoloadPaths = [];
    /**
     * @var string[]
     */
    private $bootstrapFiles = [];
    /**
     * @var string
     */
    private $indentChar = ' ';
    /**
     * @var int
     */
    private $indentSize = 4;
    /**
     * @var string[]
     */
    private $phpstanConfigs = [];
    /**
     * @var null|PhpVersion::*
     */
    private $phpVersion;
    /**
     * @var string|null
     */
    private $symfonyContainerXmlFile;
    /**
     * @var string|null
     */
    private $symfonyContainerPhpFile;
    /**
     * To make sure type declarations set and level are not duplicated,
     * as both contain same rules
     * @var bool|null
     */
    private $isTypeCoverageLevelUsed;
    /**
     * @var bool|null
     */
    private $isDeadCodeLevelUsed;
    /**
     * @var bool|null
     */
    private $isCodeQualityLevelUsed;
    /**
     * @var bool|null
     */
    private $isFluentNewLine;
    /**
     * @var RegisteredService[]
     */
    private $registerServices = [];
    /**
     * @var array<SetGroup::*>
     */
    private $setGroups = [];
    /**
     * @var string[]
     */
    private $groupLoadedSets = [];
    public function __invoke(RectorConfig $rectorConfig) : void
    {
        // @experimental 2024-06
        if ($this->setGroups !== []) {
            $setProviderCollector = $rectorConfig->make(SetProviderCollector::class);
            $setManager = new SetManager($setProviderCollector);
            $this->groupLoadedSets = $setManager->matchBySetGroups($this->setGroups);
        }
        // merge sets together
        $this->sets = \array_merge($this->sets, $this->groupLoadedSets);
        $uniqueSets = \array_unique($this->sets);
        if (\in_array(SetList::TYPE_DECLARATION, $uniqueSets, \true) && $this->isTypeCoverageLevelUsed === \true) {
            throw new InvalidConfigurationException(\sprintf('Your config already enables type declarations set.%sRemove "->withTypeCoverageLevel()" as it only duplicates it, or remove type declaration set.', \PHP_EOL));
        }
        if (\in_array(SetList::DEAD_CODE, $uniqueSets, \true) && $this->isDeadCodeLevelUsed === \true) {
            throw new InvalidConfigurationException(\sprintf('Your config already enables dead code set.%sRemove "->withDeadCodeLevel()" as it only duplicates it, or remove dead code set.', \PHP_EOL));
        }
        if (\in_array(SetList::CODE_QUALITY, $uniqueSets, \true) && $this->isCodeQualityLevelUsed === \true) {
            throw new InvalidConfigurationException(\sprintf('Your config already enables code quality set.%sRemove "->withCodeQualityLevel()" as it only duplicates it, or remove code quality set.', \PHP_EOL));
        }
        if ($uniqueSets !== []) {
            $rectorConfig->sets($uniqueSets);
        }
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
        if ($this->cacheClass !== null) {
            $rectorConfig->cacheClass($this->cacheClass);
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
    }
    /**
     * @param string[] $paths
     */
    public function withPaths(array $paths) : self
    {
        $this->paths = $paths;
        return $this;
    }
    /**
     * @param array<mixed> $skip
     */
    public function withSkip(array $skip) : self
    {
        $this->skip = \array_merge($this->skip, $skip);
        return $this;
    }
    public function withSkipPath(string $skipPath) : self
    {
        if (\strpos($skipPath, '*') === \false) {
            Assert::fileExists($skipPath);
        }
        return $this->withSkip([$skipPath]);
    }
    /**
     * Include PHP files from the root directory,
     * typically ecs.php, rector.php etc.
     */
    public function withRootFiles() : self
    {
        $gitIgnoreContents = [];
        if (\file_exists(\getcwd() . '/.gitignore')) {
            $gitIgnoreContents = \array_filter(\iterator_to_array(FileSystem::readLines(\getcwd() . '/.gitignore')), function (string $string) : bool {
                $string = \trim($string);
                // new line
                if ($string === '') {
                    return \false;
                }
                // comment
                if (\strncmp($string, '#', \strlen('#')) === 0) {
                    return \false;
                }
                // normalize
                $string = \ltrim($string, '/\\');
                // files in deep directory, no need to be in lists
                if (\strpos($string, '/') !== \false || \strpos($string, '\\') !== \false) {
                    return \false;
                }
                // only files
                return \is_file($string);
            });
            // make realpath collection
            $gitIgnoreContents = \array_map(function (string $string) : string {
                // normalize
                $string = \ltrim($string, '/\\');
                return \realpath($string);
            }, $gitIgnoreContents);
        }
        $rootPhpFilesFinder = (new Finder())->files()->in(\getcwd())->depth(0)->name('*.php');
        foreach ($rootPhpFilesFinder as $rootPhpFileFinder) {
            $path = $rootPhpFileFinder->getRealPath();
            if (\in_array($path, $gitIgnoreContents, \true)) {
                continue;
            }
            $this->paths[] = $path;
        }
        return $this;
    }
    /**
     * @param string[] $sets
     */
    public function withSets(array $sets) : self
    {
        $this->sets = \array_merge($this->sets, $sets);
        return $this;
    }
    /**
     * Upgrade your annotations to attributes
     */
    public function withAttributesSets(bool $symfony = \false, bool $doctrine = \false, bool $mongoDb = \false, bool $gedmo = \false, bool $phpunit = \false, bool $fosRest = \false, bool $jms = \false, bool $sensiolabs = \false, bool $all = \false) : self
    {
        if ($symfony || $all) {
            $this->sets[] = SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES;
        }
        if ($doctrine || $all) {
            $this->sets[] = DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES;
        }
        if ($mongoDb || $all) {
            $this->sets[] = DoctrineSetList::MONGODB__ANNOTATIONS_TO_ATTRIBUTES;
        }
        if ($gedmo || $all) {
            $this->sets[] = DoctrineSetList::GEDMO_ANNOTATIONS_TO_ATTRIBUTES;
        }
        if ($phpunit || $all) {
            $this->sets[] = PHPUnitSetList::ANNOTATIONS_TO_ATTRIBUTES;
        }
        if ($fosRest || $all) {
            $this->sets[] = FOSRestSetList::ANNOTATIONS_TO_ATTRIBUTES;
        }
        if ($jms || $all) {
            $this->sets[] = JMSSetList::ANNOTATIONS_TO_ATTRIBUTES;
        }
        if ($sensiolabs || $all) {
            $this->sets[] = SensiolabsSetList::ANNOTATIONS_TO_ATTRIBUTES;
        }
        return $this;
    }
    /**
     * make use of polyfill packages in composer.json
     */
    public function withPhpPolyfill() : self
    {
        $this->sets[] = SetList::PHP_POLYFILLS;
        return $this;
    }
    /**
     * What PHP sets should be applied? By default the same version
     * as composer.json has is used
     */
    public function withPhpSets(bool $php83 = \false, bool $php82 = \false, bool $php81 = \false, bool $php80 = \false, bool $php74 = \false, bool $php73 = \false, bool $php72 = \false, bool $php71 = \false, bool $php70 = \false, bool $php56 = \false, bool $php55 = \false, bool $php54 = \false, bool $php53 = \false, bool $php84 = \false) : self
    {
        $pickedArguments = \array_filter(\func_get_args());
        if ($pickedArguments !== []) {
            Notifier::notifyWithPhpSetsNotSuitableForPHP80();
        }
        if (\count($pickedArguments) > 1) {
            throw new InvalidConfigurationException(\sprintf('Pick only one version target in "withPhpSets()". All rules up to this version will be used.%sTo use your composer.json PHP version, keep arguments empty.', \PHP_EOL));
        }
        if ($pickedArguments === []) {
            // use composer.json PHP version
            $projectComposerJsonFilePath = \getcwd() . '/composer.json';
            if (\file_exists($projectComposerJsonFilePath)) {
                $projectPhpVersion = ProjectComposerJsonPhpVersionResolver::resolve($projectComposerJsonFilePath);
                if (\is_int($projectPhpVersion)) {
                    $this->sets[] = \Rector\Configuration\PhpLevelSetResolver::resolveFromPhpVersion($projectPhpVersion);
                    return $this;
                }
            }
            throw new InvalidConfigurationException(\sprintf('We could not find local "composer.json" to determine your PHP version.%sPlease, fill the PHP version set in withPhpSets() manually.', \PHP_EOL));
        }
        if ($php53) {
            $this->sets[] = LevelSetList::UP_TO_PHP_53;
        } elseif ($php54) {
            $this->sets[] = LevelSetList::UP_TO_PHP_54;
        } elseif ($php55) {
            $this->sets[] = LevelSetList::UP_TO_PHP_55;
        } elseif ($php56) {
            $this->sets[] = LevelSetList::UP_TO_PHP_56;
        } elseif ($php70) {
            $this->sets[] = LevelSetList::UP_TO_PHP_70;
        } elseif ($php71) {
            $this->sets[] = LevelSetList::UP_TO_PHP_71;
        } elseif ($php72) {
            $this->sets[] = LevelSetList::UP_TO_PHP_72;
        } elseif ($php73) {
            $this->sets[] = LevelSetList::UP_TO_PHP_73;
        } elseif ($php74) {
            $this->sets[] = LevelSetList::UP_TO_PHP_74;
        } elseif ($php80) {
            $this->sets[] = LevelSetList::UP_TO_PHP_80;
        } elseif ($php81) {
            $this->sets[] = LevelSetList::UP_TO_PHP_81;
        } elseif ($php82) {
            $this->sets[] = LevelSetList::UP_TO_PHP_82;
        } elseif ($php83) {
            $this->sets[] = LevelSetList::UP_TO_PHP_83;
        } elseif ($php84) {
            $this->sets[] = LevelSetList::UP_TO_PHP_84;
        }
        return $this;
    }
    /**
     * Following methods are suitable for PHP 7.4 and lower, before named args
     * Let's keep them without warning, in case Rector is run on both PHP 7.4 and PHP 8.0 in CI
     */
    public function withPhp53Sets() : self
    {
        $this->sets[] = LevelSetList::UP_TO_PHP_53;
        return $this;
    }
    public function withPhp54Sets() : self
    {
        $this->sets[] = LevelSetList::UP_TO_PHP_54;
        return $this;
    }
    public function withPhp55Sets() : self
    {
        $this->sets[] = LevelSetList::UP_TO_PHP_55;
        return $this;
    }
    public function withPhp56Sets() : self
    {
        $this->sets[] = LevelSetList::UP_TO_PHP_56;
        return $this;
    }
    public function withPhp70Sets() : self
    {
        $this->sets[] = LevelSetList::UP_TO_PHP_70;
        return $this;
    }
    public function withPhp71Sets() : self
    {
        $this->sets[] = LevelSetList::UP_TO_PHP_71;
        return $this;
    }
    public function withPhp72Sets() : self
    {
        $this->sets[] = LevelSetList::UP_TO_PHP_72;
        return $this;
    }
    public function withPhp73Sets() : self
    {
        $this->sets[] = LevelSetList::UP_TO_PHP_73;
        return $this;
    }
    public function withPhp74Sets() : self
    {
        $this->sets[] = LevelSetList::UP_TO_PHP_74;
        return $this;
    }
    // there is no withPhp80Sets() and above,
    // as we already use PHP 8.0 and should go with withPhpSets() instead
    public function withPreparedSets(
        bool $deadCode = \false,
        bool $codeQuality = \false,
        bool $codingStyle = \false,
        bool $typeDeclarations = \false,
        bool $privatization = \false,
        bool $naming = \false,
        bool $instanceOf = \false,
        bool $earlyReturn = \false,
        bool $strictBooleans = \false,
        bool $carbon = \false,
        bool $rectorPreset = \false,
        bool $phpunitCodeQuality = \false,
        bool $doctrineCodeQuality = \false,
        bool $symfonyCodeQuality = \false,
        bool $symfonyConfigs = \false,
        // composer based
        bool $twig = \false,
        bool $phpunit = \false
    ) : self
    {
        Notifier::notifyNotSuitableMethodForPHP74(__METHOD__);
        if ($deadCode) {
            $this->sets[] = SetList::DEAD_CODE;
        }
        if ($codeQuality) {
            $this->sets[] = SetList::CODE_QUALITY;
        }
        if ($codingStyle) {
            $this->sets[] = SetList::CODING_STYLE;
        }
        if ($typeDeclarations) {
            $this->sets[] = SetList::TYPE_DECLARATION;
        }
        if ($privatization) {
            $this->sets[] = SetList::PRIVATIZATION;
        }
        if ($naming) {
            $this->sets[] = SetList::NAMING;
        }
        if ($instanceOf) {
            $this->sets[] = SetList::INSTANCEOF;
        }
        if ($earlyReturn) {
            $this->sets[] = SetList::EARLY_RETURN;
        }
        if ($strictBooleans) {
            $this->sets[] = SetList::STRICT_BOOLEANS;
        }
        if ($carbon) {
            $this->sets[] = SetList::CARBON;
        }
        if ($rectorPreset) {
            $this->sets[] = SetList::RECTOR_PRESET;
        }
        if ($phpunitCodeQuality) {
            $this->sets[] = PHPUnitSetList::PHPUNIT_CODE_QUALITY;
        }
        if ($doctrineCodeQuality) {
            $this->sets[] = DoctrineSetList::DOCTRINE_CODE_QUALITY;
        }
        if ($symfonyCodeQuality) {
            $this->sets[] = SymfonySetList::SYMFONY_CODE_QUALITY;
        }
        if ($symfonyConfigs) {
            $this->sets[] = SymfonySetList::CONFIGS;
        }
        // @experimental 2024-06
        if ($twig) {
            $this->setGroups[] = SetGroup::TWIG;
        }
        if ($phpunit) {
            $this->setGroups[] = SetGroup::PHPUNIT;
        }
        return $this;
    }
    /**
     * @param array<class-string<RectorInterface>> $rules
     */
    public function withRules(array $rules) : self
    {
        $this->rules = \array_merge($this->rules, $rules);
        return $this;
    }
    /**
     * @param string[] $fileExtensions
     */
    public function withFileExtensions(array $fileExtensions) : self
    {
        $this->fileExtensions = $fileExtensions;
        return $this;
    }
    /**
     * @param class-string<CacheStorageInterface>|null $cacheClass
     */
    public function withCache(?string $cacheDirectory = null, ?string $cacheClass = null, ?string $containerCacheDirectory = null) : self
    {
        $this->cacheDirectory = $cacheDirectory;
        $this->cacheClass = $cacheClass;
        $this->containerCacheDirectory = $containerCacheDirectory;
        return $this;
    }
    /**
     * @param class-string<ConfigurableRectorInterface> $rectorClass
     * @param mixed[] $configuration
     */
    public function withConfiguredRule(string $rectorClass, array $configuration) : self
    {
        $this->rulesWithConfigurations[$rectorClass][] = $configuration;
        return $this;
    }
    public function withParallel(?int $timeoutSeconds = null, ?int $maxNumberOfProcess = null, ?int $jobSize = null) : self
    {
        $this->parallel = \true;
        if (\is_int($timeoutSeconds)) {
            $this->parallelTimeoutSeconds = $timeoutSeconds;
        }
        if (\is_int($maxNumberOfProcess)) {
            $this->parallelMaxNumberOfProcess = $maxNumberOfProcess;
        }
        if (\is_int($jobSize)) {
            $this->parallelJobSize = $jobSize;
        }
        return $this;
    }
    public function withoutParallel() : self
    {
        $this->parallel = \false;
        return $this;
    }
    public function withImportNames(bool $importNames = \true, bool $importDocBlockNames = \true, bool $importShortClasses = \true, bool $removeUnusedImports = \false) : self
    {
        $this->importNames = $importNames;
        $this->importDocBlockNames = $importDocBlockNames;
        $this->importShortClasses = $importShortClasses;
        $this->removeUnusedImports = $removeUnusedImports;
        return $this;
    }
    public function withNoDiffs() : self
    {
        $this->noDiffs = \true;
        return $this;
    }
    public function withMemoryLimit(string $memoryLimit) : self
    {
        $this->memoryLimit = $memoryLimit;
        return $this;
    }
    public function withIndent(string $indentChar = ' ', int $indentSize = 4) : self
    {
        $this->indentChar = $indentChar;
        $this->indentSize = $indentSize;
        return $this;
    }
    /**
     * @param string[] $autoloadPaths
     */
    public function withAutoloadPaths(array $autoloadPaths) : self
    {
        $this->autoloadPaths = $autoloadPaths;
        return $this;
    }
    /**
     * @param string[] $bootstrapFiles
     */
    public function withBootstrapFiles(array $bootstrapFiles) : self
    {
        $this->bootstrapFiles = $bootstrapFiles;
        return $this;
    }
    /**
     * @param string[] $phpstanConfigs
     */
    public function withPHPStanConfigs(array $phpstanConfigs) : self
    {
        $this->phpstanConfigs = $phpstanConfigs;
        return $this;
    }
    /**
     * @param PhpVersion::* $phpVersion
     */
    public function withPhpVersion(int $phpVersion) : self
    {
        $this->phpVersion = $phpVersion;
        return $this;
    }
    public function withSymfonyContainerXml(string $symfonyContainerXmlFile) : self
    {
        $this->symfonyContainerXmlFile = $symfonyContainerXmlFile;
        return $this;
    }
    public function withSymfonyContainerPhp(string $symfonyContainerPhpFile) : self
    {
        $this->symfonyContainerPhpFile = $symfonyContainerPhpFile;
        return $this;
    }
    /**
     * @experimental since 0.19.7 Raise your dead-code coverage from the safest rules
     * to more affecting ones, one level at a time
     */
    public function withDeadCodeLevel(int $level) : self
    {
        $this->isDeadCodeLevelUsed = \true;
        $levelRules = LevelRulesResolver::resolve($level, DeadCodeLevel::RULES, 'RectorConfig::withDeadCodeLevel()');
        $this->rules = \array_merge($this->rules, $levelRules);
        return $this;
    }
    /**
     * @experimental since 0.19.7 Raise your type coverage from the safest type rules
     * to more affecting ones, one level at a time
     */
    public function withTypeCoverageLevel(int $level) : self
    {
        $this->isTypeCoverageLevelUsed = \true;
        $levelRules = LevelRulesResolver::resolve($level, TypeDeclarationLevel::RULES, 'RectorConfig::withTypeCoverageLevel()');
        $this->rules = \array_merge($this->rules, $levelRules);
        return $this;
    }
    /**
     * @experimental Raise your code quality from the safest rules
     * to more affecting ones, one level at a time
     */
    public function withCodeQualityLevel(int $level) : self
    {
        $this->isCodeQualityLevelUsed = \true;
        $levelRules = LevelRulesResolver::resolve($level, CodeQualityLevel::RULES, 'RectorConfig::withCodeQualityLevel()');
        $this->rules = \array_merge($this->rules, $levelRules);
        foreach (CodeQualityLevel::RULES_WITH_CONFIGURATION as $rectorClass => $configuration) {
            $this->rulesWithConfigurations[$rectorClass][] = $configuration;
        }
        return $this;
    }
    public function withFluentCallNewLine(bool $isFluentNewLine = \true) : self
    {
        $this->isFluentNewLine = $isFluentNewLine;
        return $this;
    }
    public function registerService(string $className, ?string $alias = null, ?string $tag = null) : self
    {
        $this->registerServices[] = new RegisteredService($className, $alias, $tag);
        return $this;
    }
    public function withDowngradeSets(bool $php82 = \false, bool $php81 = \false, bool $php80 = \false, bool $php74 = \false, bool $php73 = \false, bool $php72 = \false, bool $php71 = \false) : self
    {
        $pickedArguments = \array_filter(\func_get_args());
        if (\count($pickedArguments) !== 1) {
            throw new InvalidConfigurationException('Pick only one PHP version target in "withDowngradeSets()". All rules down to this version will be used.');
        }
        if ($php82) {
            $this->sets[] = DowngradeLevelSetList::DOWN_TO_PHP_82;
        }
        if ($php81) {
            $this->sets[] = DowngradeLevelSetList::DOWN_TO_PHP_81;
        }
        if ($php80) {
            $this->sets[] = DowngradeLevelSetList::DOWN_TO_PHP_80;
        }
        if ($php74) {
            $this->sets[] = DowngradeLevelSetList::DOWN_TO_PHP_74;
        }
        if ($php73) {
            $this->sets[] = DowngradeLevelSetList::DOWN_TO_PHP_73;
        }
        if ($php72) {
            $this->sets[] = DowngradeLevelSetList::DOWN_TO_PHP_72;
        }
        if ($php71) {
            $this->sets[] = DowngradeLevelSetList::DOWN_TO_PHP_71;
        }
        return $this;
    }
}
