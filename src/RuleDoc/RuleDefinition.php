<?php

declare (strict_types=1);
namespace Rector\RuleDoc;

use Rector\RuleDoc\CodeSample\ConfiguredCodeSample;
use Rector\RuleDoc\Contract\CodeSampleInterface;
use Rector\RuleDoc\Exception\PoorDocumentationException;
use Rector\RuleDoc\Exception\ShouldNotHappenException;
/**
 * @api
 */
final class RuleDefinition
{
    /**
     * @readonly
     */
    private string $description;
    private ?string $ruleClass = null;
    private ?string $ruleFilePath = null;
    /**
     * @var CodeSampleInterface[]
     * @readonly
     */
    private array $codeSamples;
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
    public function getDescription(): string
    {
        return $this->description;
    }
    public function setRuleClass(string $ruleClass): void
    {
        $this->ruleClass = $ruleClass;
    }
    public function getRuleClass(): string
    {
        if ($this->ruleClass === null) {
            throw new ShouldNotHappenException();
        }
        return $this->ruleClass;
    }
    public function setRuleFilePath(string $ruleFilePath): void
    {
        // fir relative file path for GitHub
        $this->ruleFilePath = ltrim($ruleFilePath, '/');
    }
    public function getRuleFilePath(): string
    {
        if ($this->ruleFilePath === null) {
            throw new ShouldNotHappenException();
        }
        return $this->ruleFilePath;
    }
    public function getRuleShortClass(): string
    {
        if ($this->ruleClass === null) {
            throw new ShouldNotHappenException();
        }
        // get short class name
        return basename(str_replace('\\', '/', $this->ruleClass));
    }
    /**
     * @return CodeSampleInterface[]
     */
    public function getCodeSamples(): array
    {
        return $this->codeSamples;
    }
    public function isConfigurable(): bool
    {
        $found = \false;
        foreach ($this->codeSamples as $codeSample) {
            if ($codeSample instanceof ConfiguredCodeSample) {
                $found = \true;
                break;
            }
        }
        return $found;
    }
}
