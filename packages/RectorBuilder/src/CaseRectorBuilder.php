<?php declare(strict_types=1);

namespace Rector\RectorBuilder;

use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeChanger\ExpressionAdder;
use Rector\NodeChanger\PropertyAdder;

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

    /**
     * @var ExpressionAdder
     */
    private $expressionAdder;

    /**
     * @var PropertyAdder
     */
    private $propertyAdder;

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer, ExpressionAdder $expressionAdder, PropertyAdder $propertyAdder)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->expressionAdder = $expressionAdder;
        $this->propertyAdder = $propertyAdder;
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

        $this->caseRector = new CaseRector($this->methodCallAnalyzer);

        // @required setter DI replacement
        $this->caseRector->setExpressionAdder($this->expressionAdder);
        $this->caseRector->setPropertyToClassAdder($this->propertyAdder);

        return $this->caseRector;
    }
}
