<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions;

final class PIDinRootlineConditionMatcher extends \Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions\AbstractRootlineConditionMatcher
{
    /**
     * @var string
     */
    private const TYPE = 'PIDinRootline';
    protected function getType() : string
    {
        return self::TYPE;
    }
    protected function getExpression() : string
    {
        return 'tree.rootLineIds';
    }
}
