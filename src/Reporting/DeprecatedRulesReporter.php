<?php

declare (strict_types=1);
namespace Rector\Reporting;

use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Contract\Rector\RectorInterface;
use Rector\PhpParser\Enum\NodeGroup;
use RectorPrefix202512\Symfony\Component\Console\Style\SymfonyStyle;
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
    public function reportDeprecatedNodeTypes(): void
    {
        // helper property to avoid reporting multiple times
        static $reportedClasses = [];
        foreach ($this->rectors as $rector) {
            if (!in_array(StmtsAwareInterface::class, $rector->getNodeTypes())) {
                continue;
            }
            // already reported, skip
            if (in_array(get_class($rector), $reportedClasses, \true)) {
                continue;
            }
            $reportedClasses[] = get_class($rector);
            $this->symfonyStyle->warning(sprintf('Rector rule "%s" uses StmtsAwareInterface that is now deprecated.%sUse "%s::%s" instead.%sSee %s for more', get_class($rector), \PHP_EOL, NodeGroup::class, 'STMTS_AWARE', \PHP_EOL . \PHP_EOL, 'https://github.com/rectorphp/rector-src/pull/7679'));
        }
    }
}
