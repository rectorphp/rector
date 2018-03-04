<?php declare(strict_types=1);

namespace Rector\RectorBuilder;

use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeChanger\ExpressionAdder;
use Rector\NodeChanger\IdentifierRenamer;
use Rector\NodeChanger\PropertyAdder;

final class BuilderRectorFactory
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

    /**
     * @var IdentifierRenamer
     */
    private $identifierRenamer;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(
        MethodCallAnalyzer $methodCallAnalyzer,
        ExpressionAdder $expressionAdder,
        PropertyAdder $propertyAdder,
        IdentifierRenamer $identifierRenamer,
        NodeFactory $nodeFactory
    ) {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->expressionAdder = $expressionAdder;
        $this->propertyAdder = $propertyAdder;
        $this->identifierRenamer = $identifierRenamer;
        $this->nodeFactory = $nodeFactory;
    }

    public function create(): BuilderRector
    {
        $builderRector = new BuilderRector($this->methodCallAnalyzer, $this->identifierRenamer, $this->nodeFactory);

        // @required setter DI replacement
        $builderRector->setExpressionAdder($this->expressionAdder);
        $builderRector->setPropertyToClassAdder($this->propertyAdder);

        return $builderRector;
    }
}
