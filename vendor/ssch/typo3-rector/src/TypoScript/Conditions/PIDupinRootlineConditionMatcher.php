<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\TypoScript\Conditions;

final class PIDupinRootlineConditionMatcher extends \Ssch\TYPO3Rector\TypoScript\Conditions\AbstractRootlineConditionMatcher
{
    /**
     * @var string
     */
    private const TYPE = 'PIDupinRootline';
    protected function getType() : string
    {
        return self::TYPE;
    }
    protected function getExpression() : string
    {
        return 'tree.rootLineParentIds';
    }
}
