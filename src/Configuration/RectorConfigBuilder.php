<?php

declare (strict_types=1);
namespace Rector\Configuration;

use Rector\Bridge\SetProviderCollector;
use Rector\Bridge\SetRectorsResolver;
use Rector\Caching\Contract\ValueObject\Storage\CacheStorageInterface;
use Rector\Composer\InstalledPackageResolver;
use Rector\Config\Level\CodeQualityLevel;
use Rector\Config\Level\CodingStyleLevel;
use Rector\Config\Level\DeadCodeLevel;
use Rector\Config\Level\TypeDeclarationLevel;
use Rector\Config\RectorConfig;
use Rector\Config\RegisteredService;
use Rector\Configuration\Levels\LevelRulesResolver;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Console\Notifier;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Contract\Rector\RectorInterface;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Enum\Config\Defaults;
use Rector\Exception\Configuration\InvalidConfigurationException;
use Rector\Php\PhpVersionResolver\ComposerJsonPhpVersionResolver;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\Contract\SetProviderInterface;
use Rector\Set\Enum\SetGroup;
use Rector\Set\SetManager;
use Rector\Set\ValueObject\DowngradeLevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\FOSRestSetList;
use Rector\Symfony\Set\JMSSetList;
use Rector\Symfony\Set\SensiolabsSetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\ValueObject\Configuration\LevelOverflow;
use Rector\ValueObject\PhpVersion;
use RectorPrefix202506\Symfony\Component\Finder\Finder;
use RectorPrefix202506\Webmozart\Assert\Assert;
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
    /**
     * @var null|class-string<CacheStorageInterface>
     */
    private ?string $cacheClass = null;
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
     * To make sure type declarations set and level are not duplicated,
     * as both contain same rules
     */
    private ?bool $isTypeCoverageLevelUsed = null;
    private ?bool $isDeadCodeLevelUsed = null;
    private ?bool $isCodeQualityLevelUsed = null;
    private ?bool $isCodingStyleLevelUsed = null;
    private ?bool $isFluentNewLine = null;
    private ?bool $isTreatClassesAsFinal = null;
    /**
     * @var RegisteredService[]
     */
    private array $registerServices = [];
    /**
     * @var array<SetGroup::*>
     */
    private array $setGroups = [];
    private ?bool $reportingRealPath = null;
    /**
     * @var string[]
     */
    private array $groupLoadedSets = [];
    private ?string $editorUrl = null;
    private ?bool $isWithPhpSetsUsed = null;
    private ?bool $isWithPhpLevelUsed = null;
    /**
     * @var array<class-string<SetProviderInterface>,bool>
     */
    private array $setProviders = [];
    /**
     * @var LevelOverflow[]
     */
    private array $levelOverflows = [];
    public function __invoke(RectorConfig $rectorConfig) : void
    {
        if ($this->setGroups !== [] || $this->setProviders !== []) {
            $setProviderCollector = new SetProviderCollector(\array_map(static fn(string $setProvider): SetProviderInterface => $rectorConfig->make($setProvider), \array_keys($this->setProviders)));
            $setManager = new SetManager($setProviderCollector, new InstalledPackageResolver(\getcwd()));
            $this->groupLoadedSets = $setManager->matchBySetGroups($this->setGroups);
            SimpleParameterProvider::addParameter(\Rector\Configuration\Option::COMPOSER_BASED_SETS, $this->groupLoadedSets);
        }
        // not to miss it by accident
        if ($this->isWithPhpSetsUsed === \true) {
            $this->sets[] = SetList::PHP_POLYFILLS;
        }
        // merge sets together
        $this->sets = \array_merge($this->sets, $this->groupLoadedSets);
        $uniqueSets = \array_unique($this->sets);
        if ($this->isWithPhpLevelUsed && $this->isWithPhpSetsUsed) {
            throw new InvalidConfigurationException(\sprintf('Your config uses "withPhp*()" and "withPhpLevel()" methods at the same time.%sPick one of them to avoid rule conflicts.', \PHP_EOL));
        }
        if (\in_array(SetList::TYPE_DECLARATION, $uniqueSets, \true) && $this->isTypeCoverageLevelUsed === \true) {
            throw new InvalidConfigurationException(\sprintf('Your config already enables type declarations set.%sRemove "->withTypeCoverageLevel()" as it only duplicates it, or remove type declaration set.', \PHP_EOL));
        }
        if (\in_array(SetList::DEAD_CODE, $uniqueSets, \true) && $this->isDeadCodeLevelUsed === \true) {
            throw new InvalidConfigurationException(\sprintf('Your config already enables dead code set.%sRemove "->withDeadCodeLevel()" as it only duplicates it, or remove dead code set.', \PHP_EOL));
        }
        if (\in_array(SetList::CODE_QUALITY, $uniqueSets, \true) && $this->isCodeQualityLevelUsed === \true) {
            throw new InvalidConfigurationException(\sprintf('Your config already enables code quality set.%sRemove "->withCodeQualityLevel()" as it only duplicates it, or remove code quality set.', \PHP_EOL));
        }
        if (\in_array(SetList::CODING_STYLE, $uniqueSets, \true) && $this->isCodingStyleLevelUsed === \true) {
            throw new InvalidConfigurationException(\sprintf('Your config already enables coding style set.%sRemove "->withCodingStyleLevel()" as it only duplicates it, or remove coding style set.', \PHP_EOL));
        }
        if ($uniqueSets !== []) {
            $rectorConfig->sets($uniqueSets);
        }
        // log rules from sets and compare them with explicit rules
        $setRegisteredRectorClasses = $rectorConfig->getRectorClasses();
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
        if ($this->isTreatClassesAsFinal !== null) {
            $rectorConfig->treatClassesAsFinal($this->isTreatClassesAsFinal);
        }
        if ($this->reportingRealPath !== null) {
            $rectorConfig->reportingRealPath($this->reportingRealPath);
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
     * Include PHP files from the root directory (including hidden ones),
     * typically ecs.php, rector.php, .php-cs-fixer.dist.php etc.
     */
    public function withRootFiles() : self
    {
        $rootPhpFilesFinder = (new Finder())->files()->in(\getcwd())->depth(0)->ignoreDotFiles(\false)->ignoreVCSIgnored(\true)->name('*.php')->name('.*.php')->notName('.phpstorm.meta.php');
        foreach ($rootPhpFilesFinder as $rootPhpFileFinder) {
            $path = $rootPhpFileFinder->getRealPath();
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
    public function withAttributesSets(bool $symfony = \false, bool $doctrine = \false, bool $mongoDb = \false, bool $gedmo = \false, bool $phpunit = \false, bool $fosRest = \false, bool $jms = \false, bool $sensiolabs = \false, bool $behat = \false, bool $all = \false, bool $symfonyRoute = \false, bool $symfonyValidator = \false) : self
    {
        // if nothing is passed, enable all as convention in other method
        if (\func_get_args() === []) {
            $all = \true;
        }
        if ($symfony || $all) {
            $this->sets[] = SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES;
        }
        // dx for more granular upgrade
        if ($symfonyRoute) {
            if ($symfony) {
                throw new InvalidConfigurationException('$symfonyRoute is already included in $symfony. Use $symfony only');
            }
            $this->withConfiguredRule(AnnotationToAttributeRector::class, [new AnnotationToAttribute('Symfony\\Component\\Routing\\Annotation\\Route')]);
        }
        if ($symfonyValidator) {
            if ($symfony) {
                throw new InvalidConfigurationException('$symfonyValidator is already included in $symfony. Use $symfony only');
            }
            $this->sets[] = SymfonySetList::SYMFONY_52_VALIDATOR_ATTRIBUTES;
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
        if ($behat || $all) {
            $this->sets[] = SetList::BEHAT_ANNOTATIONS_TO_ATTRIBUTES;
        }
        return $this;
    }
    /**
     * What PHP sets should be applied? By default the same version
     * as composer.json has is used
     */
    public function withPhpSets(bool $php83 = \false, bool $php82 = \false, bool $php81 = \false, bool $php80 = \false, bool $php74 = \false, bool $php73 = \false, bool $php72 = \false, bool $php71 = \false, bool $php70 = \false, bool $php56 = \false, bool $php55 = \false, bool $php54 = \false, bool $php53 = \false, bool $php84 = \false) : self
    {
        if ($this->isWithPhpSetsUsed === \true) {
            throw new InvalidConfigurationException(\sprintf('Method "%s()" can be called only once. It always includes all previous sets UP TO the defined version.%sThe best practise is to call it once with no argument. That way it will pick up PHP version from composer.json and your project will always stay up to date.', __METHOD__, \PHP_EOL));
        }
        $this->isWithPhpSetsUsed = \true;
        $pickedArguments = \array_filter(\func_get_args());
        if ($pickedArguments !== []) {
            Notifier::errorWithPhpSetsNotSuitableForPHP74AndLower();
        }
        if (\count($pickedArguments) > 1) {
            throw new InvalidConfigurationException(\sprintf('Pick only one version target in "withPhpSets()". All rules up to this version will be used.%sTo use your composer.json PHP version, keep arguments empty.', \PHP_EOL));
        }
        if ($pickedArguments === []) {
            $projectPhpVersion = ComposerJsonPhpVersionResolver::resolveFromCwdOrFail();
            $phpLevelSets = \Rector\Configuration\PhpLevelSetResolver::resolveFromPhpVersion($projectPhpVersion);
            $this->sets = \array_merge($this->sets, $phpLevelSets);
            return $this;
        }
        if ($php53) {
            $this->withPhp53Sets();
            return $this;
        }
        if ($php54) {
            $this->withPhp54Sets();
            return $this;
        }
        if ($php55) {
            $this->withPhp55Sets();
            return $this;
        }
        if ($php56) {
            $this->withPhp56Sets();
            return $this;
        }
        if ($php70) {
            $this->withPhp70Sets();
            return $this;
        }
        if ($php71) {
            $this->withPhp71Sets();
            return $this;
        }
        if ($php72) {
            $this->withPhp72Sets();
            return $this;
        }
        if ($php73) {
            $this->withPhp73Sets();
            return $this;
        }
        if ($php74) {
            $this->withPhp74Sets();
            return $this;
        }
        if ($php80) {
            $targetPhpVersion = PhpVersion::PHP_80;
        } elseif ($php81) {
            $targetPhpVersion = PhpVersion::PHP_81;
        } elseif ($php82) {
            $targetPhpVersion = PhpVersion::PHP_82;
        } elseif ($php83) {
            $targetPhpVersion = PhpVersion::PHP_83;
        } elseif ($php84) {
            $targetPhpVersion = PhpVersion::PHP_84;
        } else {
            throw new InvalidConfigurationException('Invalid PHP version set');
        }
        $phpLevelSets = \Rector\Configuration\PhpLevelSetResolver::resolveFromPhpVersion($targetPhpVersion);
        $this->sets = \array_merge($this->sets, $phpLevelSets);
        return $this;
    }
    /**
     * Following methods are suitable for PHP 7.4 and lower, before named args
     * Let's keep them without warning, in case Rector is run on both PHP 7.4 and PHP 8.0 in CI
     */
    public function withPhp53Sets() : self
    {
        $this->isWithPhpSetsUsed = \true;
        $this->sets = \array_merge($this->sets, \Rector\Configuration\PhpLevelSetResolver::resolveFromPhpVersion(PhpVersion::PHP_53));
        return $this;
    }
    public function withPhp54Sets() : self
    {
        $this->isWithPhpSetsUsed = \true;
        $this->sets = \array_merge($this->sets, \Rector\Configuration\PhpLevelSetResolver::resolveFromPhpVersion(PhpVersion::PHP_54));
        return $this;
    }
    public function withPhp55Sets() : self
    {
        $this->isWithPhpSetsUsed = \true;
        $this->sets = \array_merge($this->sets, \Rector\Configuration\PhpLevelSetResolver::resolveFromPhpVersion(PhpVersion::PHP_55));
        return $this;
    }
    public function withPhp56Sets() : self
    {
        $this->isWithPhpSetsUsed = \true;
        $this->sets = \array_merge($this->sets, \Rector\Configuration\PhpLevelSetResolver::resolveFromPhpVersion(PhpVersion::PHP_56));
        return $this;
    }
    public function withPhp70Sets() : self
    {
        $this->isWithPhpSetsUsed = \true;
        $this->sets = \array_merge($this->sets, \Rector\Configuration\PhpLevelSetResolver::resolveFromPhpVersion(PhpVersion::PHP_70));
        return $this;
    }
    public function withPhp71Sets() : self
    {
        $this->isWithPhpSetsUsed = \true;
        $this->sets = \array_merge($this->sets, \Rector\Configuration\PhpLevelSetResolver::resolveFromPhpVersion(PhpVersion::PHP_71));
        return $this;
    }
    public function withPhp72Sets() : self
    {
        $this->isWithPhpSetsUsed = \true;
        $this->sets = \array_merge($this->sets, \Rector\Configuration\PhpLevelSetResolver::resolveFromPhpVersion(PhpVersion::PHP_72));
        return $this;
    }
    public function withPhp73Sets() : self
    {
        $this->isWithPhpSetsUsed = \true;
        $this->sets = \array_merge($this->sets, \Rector\Configuration\PhpLevelSetResolver::resolveFromPhpVersion(PhpVersion::PHP_73));
        return $this;
    }
    public function withPhp74Sets() : self
    {
        $this->isWithPhpSetsUsed = \true;
        $this->sets = \array_merge($this->sets, \Rector\Configuration\PhpLevelSetResolver::resolveFromPhpVersion(PhpVersion::PHP_74));
        return $this;
    }
    // there is no withPhp80Sets() and above,
    // as we already use PHP 8.0 and should go with withPhpSets() instead
    public function withPreparedSets(bool $deadCode = \false, bool $codeQuality = \false, bool $codingStyle = \false, bool $typeDeclarations = \false, bool $privatization = \false, bool $naming = \false, bool $instanceOf = \false, bool $earlyReturn = \false, bool $strictBooleans = \false, bool $carbon = \false, bool $rectorPreset = \false, bool $phpunitCodeQuality = \false, bool $doctrineCodeQuality = \false, bool $symfonyCodeQuality = \false, bool $symfonyConfigs = \false) : self
    {
        Notifier::notifyNotSuitableMethodForPHP74(__METHOD__);
        $setMap = [SetList::DEAD_CODE => $deadCode, SetList::CODE_QUALITY => $codeQuality, SetList::CODING_STYLE => $codingStyle, SetList::TYPE_DECLARATION => $typeDeclarations, SetList::PRIVATIZATION => $privatization, SetList::NAMING => $naming, SetList::INSTANCEOF => $instanceOf, SetList::EARLY_RETURN => $earlyReturn, SetList::STRICT_BOOLEANS => $strictBooleans, SetList::CARBON => $carbon, SetList::RECTOR_PRESET => $rectorPreset, PHPUnitSetList::PHPUNIT_CODE_QUALITY => $phpunitCodeQuality, DoctrineSetList::DOCTRINE_CODE_QUALITY => $doctrineCodeQuality, SymfonySetList::SYMFONY_CODE_QUALITY => $symfonyCodeQuality, SymfonySetList::CONFIGS => $symfonyConfigs];
        foreach ($setMap as $setPath => $isEnabled) {
            if ($isEnabled) {
                $this->sets[] = $setPath;
            }
        }
        return $this;
    }
    public function withComposerBased(bool $twig = \false, bool $doctrine = \false, bool $phpunit = \false, bool $symfony = \false, bool $netteUtils = \false) : self
    {
        $setMap = [SetGroup::TWIG => $twig, SetGroup::DOCTRINE => $doctrine, SetGroup::PHPUNIT => $phpunit, SetGroup::SYMFONY => $symfony, SetGroup::NETTE_UTILS => $netteUtils];
        foreach ($setMap as $setPath => $isEnabled) {
            if ($isEnabled) {
                $this->setGroups[] = $setPath;
            }
        }
        return $this;
    }
    /**
     * @param array<class-string<RectorInterface>> $rules
     */
    public function withRules(array $rules) : self
    {
        $this->rules = \array_merge($this->rules, $rules);
        if (SimpleParameterProvider::provideBoolParameter(\Rector\Configuration\Option::IS_RECTORCONFIG_BUILDER_RECREATED, \false) === \false) {
            // log all explicitly registered rules on root rector.php
            // we only check the non-configurable rules, as the configurable ones might override them
            $nonConfigurableRules = \array_filter($rules, fn(string $rule): bool => !\is_a($rule, ConfigurableRectorInterface::class, \true));
            SimpleParameterProvider::addParameter(\Rector\Configuration\Option::ROOT_STANDALONE_REGISTERED_RULES, $nonConfigurableRules);
        }
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
        if (\substr_compare($symfonyContainerXmlFile, '.xml', -\strlen('.xml')) !== 0) {
            throw new InvalidConfigurationException(\sprintf('Provided dumped Symfony container must have "xml" suffix. "%s" given', $symfonyContainerXmlFile));
        }
        $this->symfonyContainerXmlFile = $symfonyContainerXmlFile;
        return $this;
    }
    public function withSymfonyContainerPhp(string $symfonyContainerPhpFile) : self
    {
        if (\substr_compare($symfonyContainerPhpFile, '.php', -\strlen('.php')) !== 0) {
            throw new InvalidConfigurationException(\sprintf('Provided dumped Symfony container must have "php" suffix. "%s" given', $symfonyContainerPhpFile));
        }
        $this->symfonyContainerPhpFile = $symfonyContainerPhpFile;
        return $this;
    }
    /**
     * Raise your type coverage from the safest type rules
     * to more affecting ones, one level at a time
     */
    public function withTypeCoverageLevel(int $level) : self
    {
        Assert::natural($level);
        $this->isTypeCoverageLevelUsed = \true;
        $levelRules = LevelRulesResolver::resolve($level, TypeDeclarationLevel::RULES, __METHOD__);
        // too high
        $levelRulesCount = \count($levelRules);
        if ($levelRulesCount + self::MAX_LEVEL_GAP < $level) {
            $this->levelOverflows[] = new LevelOverflow('withTypeCoverageLevel', $level, $levelRulesCount, 'typeDeclarations', 'TYPE_DECLARATION');
        }
        $this->rules = \array_merge($this->rules, $levelRules);
        return $this;
    }
    /**
     * Raise your dead-code coverage from the safest rules
     * to more affecting ones, one level at a time
     */
    public function withDeadCodeLevel(int $level) : self
    {
        Assert::natural($level);
        $this->isDeadCodeLevelUsed = \true;
        $levelRules = LevelRulesResolver::resolve($level, DeadCodeLevel::RULES, __METHOD__);
        // too high
        $levelRulesCount = \count($levelRules);
        if ($levelRulesCount + self::MAX_LEVEL_GAP < $level) {
            $this->levelOverflows[] = new LevelOverflow('withDeadCodeLevel', $level, $levelRulesCount, 'deadCode', 'DEAD_CODE');
        }
        $this->rules = \array_merge($this->rules, $levelRules);
        return $this;
    }
    /**
     * Raise your PHP level from, one level at a time
     */
    public function withPhpLevel(int $level) : self
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
                continue;
            }
            if (\is_string($rectorRuleWithConfiguration)) {
                $this->rules[] = $rectorRuleWithConfiguration;
            } elseif (\is_array($rectorRuleWithConfiguration)) {
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
    public function withCodeQualityLevel(int $level) : self
    {
        Assert::natural($level);
        $this->isCodeQualityLevelUsed = \true;
        $levelRules = LevelRulesResolver::resolve($level, CodeQualityLevel::RULES, __METHOD__);
        // too high
        $levelRulesCount = \count($levelRules);
        if ($levelRulesCount + self::MAX_LEVEL_GAP < $level) {
            $this->levelOverflows[] = new LevelOverflow('withCodeQualityLevel', $level, $levelRulesCount, 'codeQuality', 'CODE_QUALITY');
        }
        $this->rules = \array_merge($this->rules, $levelRules);
        foreach (CodeQualityLevel::RULES_WITH_CONFIGURATION as $rectorClass => $configuration) {
            $this->rulesWithConfigurations[$rectorClass][] = $configuration;
        }
        return $this;
    }
    /**
     * Raise your coding style from the safest rules
     * to more affecting ones, one level at a time
     */
    public function withCodingStyleLevel(int $level) : self
    {
        Assert::natural($level);
        $this->isCodingStyleLevelUsed = \true;
        $levelRules = LevelRulesResolver::resolve($level, CodingStyleLevel::RULES, __METHOD__);
        // too high
        $levelRulesCount = \count($levelRules);
        if ($levelRulesCount + self::MAX_LEVEL_GAP < $level) {
            $this->levelOverflows[] = new LevelOverflow('withCodingStyleLevel', $level, $levelRulesCount, 'codingStyle', 'CODING_STYLE');
        }
        $this->rules = \array_merge($this->rules, $levelRules);
        foreach (CodingStyleLevel::RULES_WITH_CONFIGURATION as $rectorClass => $configuration) {
            $this->rulesWithConfigurations[$rectorClass][] = $configuration;
        }
        return $this;
    }
    public function withFluentCallNewLine(bool $isFluentNewLine = \true) : self
    {
        $this->isFluentNewLine = $isFluentNewLine;
        return $this;
    }
    public function withTreatClassesAsFinal(bool $isTreatClassesAsFinal = \true) : self
    {
        $this->isTreatClassesAsFinal = $isTreatClassesAsFinal;
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
        } elseif ($php81) {
            $this->sets[] = DowngradeLevelSetList::DOWN_TO_PHP_81;
        } elseif ($php80) {
            $this->sets[] = DowngradeLevelSetList::DOWN_TO_PHP_80;
        } elseif ($php74) {
            $this->sets[] = DowngradeLevelSetList::DOWN_TO_PHP_74;
        } elseif ($php73) {
            $this->sets[] = DowngradeLevelSetList::DOWN_TO_PHP_73;
        } elseif ($php72) {
            $this->sets[] = DowngradeLevelSetList::DOWN_TO_PHP_72;
        } elseif ($php71) {
            $this->sets[] = DowngradeLevelSetList::DOWN_TO_PHP_71;
        }
        return $this;
    }
    public function withRealPathReporting(bool $absolutePath = \true) : self
    {
        $this->reportingRealPath = $absolutePath;
        return $this;
    }
    public function withEditorUrl(string $editorUrl) : self
    {
        $this->editorUrl = $editorUrl;
        return $this;
    }
    /**
     * @param class-string<SetProviderInterface> ...$setProviders
     */
    public function withSetProviders(string ...$setProviders) : self
    {
        foreach ($setProviders as $setProvider) {
            if (\array_key_exists($setProvider, $this->setProviders)) {
                continue;
            }
            if (!\is_a($setProvider, SetProviderInterface::class, \true)) {
                throw new InvalidConfigurationException(\sprintf('Set provider "%s" must implement "%s"', $setProvider, SetProviderInterface::class));
            }
            $this->setProviders[$setProvider] = \true;
        }
        return $this;
    }
}
