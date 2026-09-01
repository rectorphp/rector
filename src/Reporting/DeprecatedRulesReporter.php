<?php

declare (strict_types=1);
namespace Rector\Reporting;

use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Contract\Rector\RectorInterface;
use Rector\PhpParser\Node\FileNode;
use ReflectionMethod;
use RectorPrefix202609\Symfony\Component\Console\Style\SymfonyStyle;
final class DeprecatedRulesReporter
{
    /**
     * @readonly
     */
    private SymfonyStyle $symfonyStyle;
    /**
     * @var RectorInterface[]
     * @readonly
     */
    private array $rectors;
    /**
     * @param RectorInterface[] $rectors
     */
    public function __construct(SymfonyStyle $symfonyStyle, array $rectors)
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->rectors = $rectors;
    }
    public function reportDeprecatedRules(): void
    {
        /** @var string[] $registeredRectorRules */
        $registeredRectorRules = SimpleParameterProvider::provideArrayParameter(Option::REGISTERED_RECTOR_RULES);
        foreach ($registeredRectorRules as $registeredRectorRule) {
            if (!is_a($registeredRectorRule, DeprecatedInterface::class, \true)) {
                continue;
            }
            $this->symfonyStyle->warning(sprintf('Registered rule "%s" is deprecated and will be removed. Upgrade your config to use another rule or remove it', $registeredRectorRule));
        }
    }
    public function reportDeprecatedSkippedRules(): void
    {
        /** @var string[] $skippedRectorRules */
        $skippedRectorRules = SimpleParameterProvider::provideArrayParameter(Option::SKIPPED_RECTOR_RULES);
        foreach ($skippedRectorRules as $skippedRectorRule) {
            if (!is_a($skippedRectorRule, DeprecatedInterface::class, \true)) {
                continue;
            }
            $this->symfonyStyle->warning(sprintf('Skipped rule "%s" is deprecated', $skippedRectorRule));
        }
    }
    public function reportDeprecatedCacheMetaExtensions(): void
    {
        /** @var string[] $cacheMetaExtensions */
        $cacheMetaExtensions = SimpleParameterProvider::provideArrayParameter(Option::CACHE_META_EXTENSIONS);
        foreach ($cacheMetaExtensions as $cacheMetumExtension) {
            $this->symfonyStyle->warning(sprintf('Cache meta extension "%s" is deprecated and no longer applied. It is a niche mechanism, let Rector handle cache on its own. If custom invalidation is needed, handle it in CI in a more generic way, e.g. by clearing the cache directory.', $cacheMetumExtension));
        }
    }
    public function reportDeprecatedPhpSetsMethods(): void
    {
        /** @var string[] $deprecatedPhpSetsMethods */
        $deprecatedPhpSetsMethods = SimpleParameterProvider::provideArrayParameter(Option::DEPRECATED_PHP_SETS_METHODS);
        foreach (array_unique($deprecatedPhpSetsMethods) as $deprecatedPhpSetsMethod) {
            $this->symfonyStyle->warning(sprintf('The "->%s()" method is deprecated and no longer applied. Use "->withPhpLevel()" instead, to raise PHP level one rule at a time.', $deprecatedPhpSetsMethod));
        }
    }
    public function reportDeprecatedAttributesSetsArgs(): void
    {
        /** @var string[] $deprecatedAttributesSetsArgs */
        $deprecatedAttributesSetsArgs = SimpleParameterProvider::provideArrayParameter(Option::DEPRECATED_ATTRIBUTES_SETS_ARGS);
        foreach (array_unique($deprecatedAttributesSetsArgs) as $deprecatedAttributesSetsArg) {
            $this->symfonyStyle->warning(sprintf('The "->withAttributesSets(%s: true)" argument is deprecated and no longer applied. It is already included in the "symfony: true" argument, use it instead.', $deprecatedAttributesSetsArg));
        }
    }
    public function reportDeprecatedComposerBasedArgs(): void
    {
        /** @var string[] $deprecatedComposerBasedArgs */
        $deprecatedComposerBasedArgs = SimpleParameterProvider::provideArrayParameter(Option::DEPRECATED_COMPOSER_BASED_ARGS);
        foreach (array_unique($deprecatedComposerBasedArgs) as $deprecatedComposerBasedArg) {
            $this->symfonyStyle->warning(sprintf('The "->withComposerBased(%s: true)" argument is deprecated and no longer applied. It only added named args to 2 methods of a single package, register the rule directly if needed.', $deprecatedComposerBasedArg));
        }
    }
    public function reportDeprecatedRectorUnsupportedMethods(): void
    {
        // to be added in related PR
        if (!class_exists(FileNode::class)) {
            return;
        }
        foreach ($this->rectors as $rector) {
            $beforeTraverseMethodReflection = new ReflectionMethod($rector, 'beforeTraverse');
            if (\PHP_VERSION_ID < 80100) {
                $beforeTraverseMethodReflection->setAccessible(\true);
            }
            if ($beforeTraverseMethodReflection->getDeclaringClass()->getName() === get_class($rector)) {
                $this->symfonyStyle->warning(sprintf('Rector rule "%s" uses deprecated "beforeTraverse" method. It should not be used, as will be marked as final. Not part of RectorInterface contract. Use "%s" to hook into file-level changes instead.', get_class($rector), FileNode::class));
            }
        }
    }
}
