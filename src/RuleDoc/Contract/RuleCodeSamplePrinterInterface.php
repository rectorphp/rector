<?php

declare (strict_types=1);
namespace Rector\RuleDoc\Contract;

use Rector\RuleDoc\RuleDefinition;
/**
 * @api
 */
interface RuleCodeSamplePrinterInterface
{
    public function isMatch(string $class): bool;
    /**
     * @return string[]
     */
    public function print(\Rector\RuleDoc\Contract\CodeSampleInterface $codeSample, RuleDefinition $ruleDefinition): array;
}
