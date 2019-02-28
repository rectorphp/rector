<?php declare(strict_types=1);

namespace Rector\Rector;

/**
 * Helper trait that uses Symfony Dependency Injection, based on @required annotation - do not use outside!
 * It gets all useful services into single Rector.
 *
 * Use only in "Abstract*Rector" classes to prevent monolith-lock.
 */
trait AbstractRectorTrait
{
    use AppliedRectorCollectorTrait;
    use NodeTypeResolverTrait;
    use NameResolverTrait;
    use ConstFetchAnalyzerTrait;
    use BetterStandardPrinterTrait;
    use NodeCommandersTrait;
    use NodeFactoryTrait;
    use VisibilityTrait;
}
