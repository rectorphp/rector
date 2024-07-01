<?php

declare (strict_types=1);
namespace Rector\Configuration\Levels;

use Rector\Contract\Rector\RectorInterface;
use RectorPrefix202407\Webmozart\Assert\Assert;
final class LevelRulesResolver
{
    /**
     * @param array<class-string<RectorInterface>> $availableRules
     * @return array<class-string<RectorInterface>>
     */
    public static function resolve(int $level, array $availableRules, string $methodName) : array
    {
        $rulesCount = \count($availableRules);
        Assert::range($level, 0, $rulesCount - 1, 'Level %s is not available "' . $methodName . '" method. Pick one between %2$s (lowest) and %3$s (highest).');
        $levelRules = [];
        for ($i = 0; $i <= $level; ++$i) {
            $levelRules[] = $availableRules[$i];
        }
        return $levelRules;
    }
}
