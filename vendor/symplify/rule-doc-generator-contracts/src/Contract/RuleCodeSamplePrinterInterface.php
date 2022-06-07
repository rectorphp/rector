<?php

declare (strict_types=1);
namespace Symplify\RuleDocGenerator\Contract;

use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
interface RuleCodeSamplePrinterInterface
{
    public function isMatch(string $class) : bool;
    /**
     * @return string[]
     */
    public function print(\Symplify\RuleDocGenerator\Contract\CodeSampleInterface $codeSample, RuleDefinition $ruleDefinition) : array;
}
