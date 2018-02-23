<?php declare(strict_types=1);

namespace Rector\RectorBuilder;

use Rector\NodeAnalyzer\MethodCallAnalyzer;

final class CaseRectorBuilder
{
    /**
     * @var CaseRector|null
     */
    private $caseRector;

    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
    }

    public function matchMethodCallByType(string $methodCallType): self
    {
        $this->getCaseRector()->setMethodCallType($methodCallType);
        return $this;
    }

    public function matchMethodName(string $methodName): self
    {
        $this->getCaseRector()->setMethodName($methodName);
        return $this;
    }

    public function changeMethodNameTo(string $newMethodName): self
    {
        $this->getCaseRector()->setNewMethodName($newMethodName);
        return $this;
    }

    public function addArgument(int $position, $value): self
    {
        $this->getCaseRector()->addNewArgument($position, $value);
        return $this;
    }

    public function build(): CaseRector
    {
        $caseRector = $this->caseRector;
        $this->caseRector = null;

        return $caseRector;
    }

    private function getCaseRector(): CaseRector
    {
        if ($this->caseRector) {
            return $this->caseRector;
        }

        return $this->caseRector = new CaseRector($this->methodCallAnalyzer);
    }
}
