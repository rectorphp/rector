<?php declare(strict_types=1);

namespace Rector\RectorBuilder;

use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeChanger\ExpressionAdder;
use Rector\NodeChanger\PropertyAdder;

final class CaseRectorBuilder
{
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

    public function __construct(
        MethodCallAnalyzer $methodCallAnalyzer,
        ExpressionAdder $expressionAdder,
        PropertyAdder $propertyAdder
    ) {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->expressionAdder = $expressionAdder;
        $this->propertyAdder = $propertyAdder;
    }

    public function create(): CaseRector
    {
        $caseRector = new CaseRector($this->methodCallAnalyzer);

        // @required setter DI replacement
        $caseRector->setExpressionAdder($this->expressionAdder);
        $caseRector->setPropertyToClassAdder($this->propertyAdder);

        return $caseRector;
    }
}
