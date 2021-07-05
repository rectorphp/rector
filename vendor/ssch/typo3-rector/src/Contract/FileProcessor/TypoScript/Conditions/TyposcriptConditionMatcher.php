<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\Conditions;

interface TyposcriptConditionMatcher
{
    /**
     * @var array<string, string>
     */
    public const OPERATOR_MAPPING = ['=' => '==', '>=' => '>=', '<=' => '<=', '>' => '>', '<' => '<', '!=' => '!='];
    /**
     * @var string
     */
    public const ALLOWED_OPERATORS_REGEX = '\\<\\=|\\>\\=|\\!\\=|\\=|\\>|\\<';
    /**
     * @var string
     */
    public const ZERO_ONE_OR_MORE_WHITESPACES = '\\s*';
    /**
     * @var string
     */
    public const CONTAINS_CONSTANT = '{$';
    /**
     * If we return null it means conditions can be removed
     * @param string $condition
     */
    public function change($condition) : ?string;
    /**
     * @param string $condition
     */
    public function shouldApply($condition) : bool;
}
