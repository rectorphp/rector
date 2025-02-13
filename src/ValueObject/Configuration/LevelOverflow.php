<?php

declare (strict_types=1);
namespace Rector\ValueObject\Configuration;

final class LevelOverflow
{
    /**
     * @readonly
     */
    private string $configurationName;
    /**
     * @readonly
     */
    private int $level;
    /**
     * @readonly
     */
    private int $ruleCount;
    /**
     * @readonly
     */
    private string $suggestedRuleset;
    /**
     * @readonly
     */
    private string $suggestedSetListConstant;
    public function __construct(string $configurationName, int $level, int $ruleCount, string $suggestedRuleset, string $suggestedSetListConstant)
    {
        $this->configurationName = $configurationName;
        $this->level = $level;
        $this->ruleCount = $ruleCount;
        $this->suggestedRuleset = $suggestedRuleset;
        $this->suggestedSetListConstant = $suggestedSetListConstant;
    }
    public function getConfigurationName() : string
    {
        return $this->configurationName;
    }
    public function getLevel() : int
    {
        return $this->level;
    }
    public function getRuleCount() : int
    {
        return $this->ruleCount;
    }
    public function getSuggestedRuleset() : string
    {
        return $this->suggestedRuleset;
    }
    public function getSuggestedSetListConstant() : string
    {
        return $this->suggestedSetListConstant;
    }
}
