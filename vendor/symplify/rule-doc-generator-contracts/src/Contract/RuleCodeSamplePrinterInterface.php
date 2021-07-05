<?php

declare (strict_types=1);
namespace Symplify\RuleDocGenerator\Contract;

use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
interface RuleCodeSamplePrinterInterface
{
    /**
     * @param string $class
     */
    public function isMatch($class) : bool;
    /**
     * @return string[]
     * @param \Symplify\RuleDocGenerator\Contract\CodeSampleInterface $codeSample
     * @param \Symplify\RuleDocGenerator\ValueObject\RuleDefinition $ruleDefinition
     */
    public function print($codeSample, $ruleDefinition) : array;
}
