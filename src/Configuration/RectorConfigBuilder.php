<?php

declare (strict_types=1);
namespace Rector\Configuration;

use Rector\Caching\Contract\ValueObject\Storage\CacheStorageInterface;
use Rector\Config\Level\DeadCodeLevel;
use Rector\Config\Level\TypeDeclarationLevel;
use Rector\Config\RectorConfig;
use Rector\Config\RegisteredService;
use Rector\Configuration\Levels\LevelRulesResolver;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Contract\Rector\RectorInterface;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Exception\Configuration\InvalidConfigurationException;
use Rector\Php\PhpVersionResolver\ProjectComposerJsonPhpVersionResolver;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\FOSRestSetList;
use Rector\Symfony\Set\JMSSetList;
use Rector\Symfony\Set\SensiolabsSetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\ValueObject\PhpVersion;
use RectorPrefix202402\Symfony\Component\Finder\Finder;
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
     * Enabled by default
     * @var bool
     */
    private $parallel = \true;
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
     * @var bool
     */
    private $isTypeCoverageLevelUsed = \false;
    /**
     * @var bool
     */
    private $isDeadCodeLevelUsed = \false;
    /**
     * @var RegisteredService[]
     */
    private $registerServices = [];
    public function __invoke(RectorConfig $rectorConfig) : void
    {
        $uniqueSets = \array_unique($this->sets);
        if (\in_array(SetList::TYPE_DECLARATION, $uniqueSets, \true) && $this->isTypeCoverageLevelUsed) {
            throw new InvalidConfigurationException(\sprintf('Your config already enables type declarations set.%sRemove "->withTypeCoverageLevel()" as it only duplicates it, or remove type declaration set.', \PHP_EOL));
        }
        if (\in_array(SetList::DEAD_CODE, $uniqueSets, \true) && $this->isDeadCodeLevelUsed) {
            throw new InvalidConfigurationException(\sprintf('Your config already enables dead code set.%sRemove "->withDeadCodeLevel()" as it only duplicates it, or remove dead code set.', \PHP_EOL));
        }
        $rectorConfig->sets($uniqueSets);
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
        $rectorConfig->skip($this->skip);
        $rectorConfig->rules($this->rules);
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
        if ($this->parallel) {
            $rectorConfig->parallel($this->parallelTimeoutSeconds, $this->parallelMaxNumberOfProcess, $this->parallelJobSize);
        } else {
            $rectorConfig->disableParallel();
        }
        if ($this->symfonyContainerXmlFile !== null) {
            $rectorConfig->symfonyContainerXml($this->symfonyContainerXmlFile);
        }
        if ($this->symfonyContainerPhpFile !== null) {
            $rectorConfig->symfonyContainerPhp($this->symfonyContainerPhpFile);
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
        $this->skip = $skip;
        return $this;
    }
    /**
     * Include PHP files from the root directory,
     * typically ecs.php, rector.php etc.
     */
    public function withRootFiles() : self
    {
        $rootPhpFilesFinder = (new Finder())->files()->in(\getcwd())->depth(0)->name('*.php');
        foreach ($rootPhpFilesFinder as $rootPhpFileFinder) {
            $this->paths[] = $rootPhpFileFinder->getRealPath();
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
    public function withAttributesSets(bool $symfony = \false, bool $doctrine = \false, bool $mongoDb = \false, bool $gedmo = \false, bool $phpunit = \false, bool $fosRest = \false, bool $jms = \false, bool $sensiolabs = \false) : self
    {
        if ($symfony) {
            $this->sets[] = SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES;
        }
        if ($doctrine) {
            $this->sets[] = DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES;
        }
        if ($mongoDb) {
            $this->sets[] = DoctrineSetList::MONGODB__ANNOTATIONS_TO_ATTRIBUTES;
        }
        if ($gedmo) {
            $this->sets[] = DoctrineSetList::GEDMO_ANNOTATIONS_TO_ATTRIBUTES;
        }
        if ($phpunit) {
            $this->sets[] = PHPUnitSetList::ANNOTATIONS_TO_ATTRIBUTES;
        }
        if ($fosRest) {
            $this->sets[] = FOSRestSetList::ANNOTATIONS_TO_ATTRIBUTES;
        }
        if ($jms) {
            $this->sets[] = JMSSetList::ANNOTATIONS_TO_ATTRIBUTES;
        }
        if ($sensiolabs) {
            $this->sets[] = SensiolabsSetList::ANNOTATIONS_TO_ATTRIBUTES;
        }
        return $this;
    }
    /**
     * What PHP sets should be applied? By default the same version
     * as composer.json has is used
     */
    public function withPhpSets(bool $php83 = \false, bool $php82 = \false, bool $php81 = \false, bool $php80 = \false, bool $php74 = \false, bool $php73 = \false, bool $php72 = \false, bool $php71 = \false, bool $php70 = \false, bool $php56 = \false, bool $php55 = \false, bool $php54 = \false, bool $php53 = \false) : self
    {
        $pickedArguments = \array_filter(\func_get_args());
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
        }
        return $this;
    }
    public function withPreparedSets(bool $deadCode = \false, bool $codeQuality = \false, bool $codingStyle = \false, bool $typeDeclarations = \false, bool $privatization = \false, bool $naming = \false, bool $instanceOf = \false, bool $earlyReturn = \false, bool $strictBooleans = \false) : self
    {
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
        return $this;
    }
    /**
     * @param array<class-string<RectorInterface>> $rules
     */
    public function withRules(array $rules) : self
    {
        $this->rules = $rules;
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
    public function registerService(string $className, ?string $alias = null, ?string $tag = null) : self
    {
        $this->registerServices[] = new RegisteredService($className, $alias, $tag);
        return $this;
    }
}
