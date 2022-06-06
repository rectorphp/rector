<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions;

final class PIDinRootlineConditionMatcher extends AbstractRootlineConditionMatcher
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
