<?php

declare (strict_types=1);
namespace Rector\Configuration\Levels;

use Rector\Contract\Rector\RectorInterface;
use Rector\Exception\ShouldNotHappenException;
use RectorPrefix202410\Webmozart\Assert\Assert;
final class LevelRulesResolver
{
    /**
     * @param array<class-string<RectorInterface>> $availableRules
     * @return array<class-string<RectorInterface>>
     */
    public static function resolve(int $level, array $availableRules, string $methodName) : array
    {
        // level < 0 is not allowed
        Assert::natural($level, \sprintf('Level must be >= 0 on %s', $methodName));
        Assert::allIsAOf($availableRules, RectorInterface::class);
        $rulesCount = \count($availableRules);
        if ($availableRules === []) {
            throw new ShouldNotHappenException(\sprintf('There are no available rules in "%s()", define the available rules first', $methodName));
        }
        // start with 0
        $maxLevel = $rulesCount - 1;
        if ($level > $maxLevel) {
            $level = $maxLevel;
        }
        $levelRules = [];
        for ($i = 0; $i <= $level; ++$i) {
            $levelRules[] = $availableRules[$i];
        }
        return $levelRules;
    }
}
