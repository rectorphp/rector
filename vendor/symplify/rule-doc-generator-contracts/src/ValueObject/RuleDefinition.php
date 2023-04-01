<?php

declare (strict_types=1);
namespace Symplify\RuleDocGenerator\ValueObject;

use RectorPrefix202304\Nette\Utils\Strings;
use Symplify\RuleDocGenerator\Contract\CodeSampleInterface;
use Symplify\RuleDocGenerator\Exception\PoorDocumentationException;
use Symplify\RuleDocGenerator\Exception\ShouldNotHappenException;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
final class RuleDefinition
{
    /**
     * @var string|null
     */
    private $ruleClass;
    /**
     * @var string|null
     */
    private $ruleFilePath;
    /**
     * @var CodeSampleInterface[]
     */
    private $codeSamples = [];
    /**
     * @readonly
     * @var string
     */
    private $description;
    /**
     * @param CodeSampleInterface[] $codeSamples
     */
    public function __construct(string $description, array $codeSamples)
    {
        $this->description = $description;
        if ($codeSamples === []) {
            throw new PoorDocumentationException('Provide at least one code sample, so people can practically see what the rule does');
        }
        $this->codeSamples = $codeSamples;
    }
    public function getDescription() : string
    {
        return $this->description;
    }
    public function setRuleClass(string $ruleClass) : void
    {
        $this->ruleClass = $ruleClass;
    }
    public function getRuleClass() : string
    {
        if ($this->ruleClass === null) {
            throw new ShouldNotHappenException();
        }
        return $this->ruleClass;
    }
    public function setRuleFilePath(string $ruleFilePath) : void
    {
        // fir relative file path for GitHub
        $this->ruleFilePath = \ltrim($ruleFilePath, '/');
    }
    public function getRuleFilePath() : string
    {
        if ($this->ruleFilePath === null) {
            throw new ShouldNotHappenException();
        }
        return $this->ruleFilePath;
    }
    public function getRuleShortClass() : string
    {
        if ($this->ruleClass === null) {
            throw new ShouldNotHappenException();
        }
        return (string) Strings::after($this->ruleClass, '\\', -1);
    }
    /**
     * @return CodeSampleInterface[]
     */
    public function getCodeSamples() : array
    {
        return $this->codeSamples;
    }
    public function isConfigurable() : bool
    {
        foreach ($this->codeSamples as $codeSample) {
            if ($codeSample instanceof ConfiguredCodeSample) {
                return \true;
            }
        }
        return \false;
    }
}
