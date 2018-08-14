<?php declare(strict_types=1);

namespace Rector\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\Node\MethodCallNodeFactory;
use Rector\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class PropertyAssignToMethodCallRector extends AbstractRector
{
    /**
     * @var PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;

    /**
     * @var MethodCallNodeFactory
     */
    private $methodCallNodeFactory;

    /**
     * @var string[]
     */
    private $types = [];

    /**
     * @var string
     */
    private $oldPropertyName;

    /**
     * @var string
     */
    private $newMethodName;

    /**
     * @todo check via https://github.com/rectorphp/rector/issues/548
     * @param string[] $types
     */
    public function __construct(
        PropertyFetchAnalyzer $propertyFetchAnalyzer,
        MethodCallNodeFactory $methodCallNodeFactory,
        array $types,
        string $oldPropertyName,
        string $newMethodName
    ) {
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->methodCallNodeFactory = $methodCallNodeFactory;
        $this->types = $types;
        $this->oldPropertyName = $oldPropertyName;
        $this->newMethodName = $newMethodName;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns property assign of specific type and property name to method call', [
            new ConfiguredCodeSample(
<<<'CODE_SAMPLE'
$someObject = new SomeClass; 
$someObject->oldProperty = false;
CODE_SAMPLE
                ,
<<<'CODE_SAMPLE'
$someObject = new SomeClass;
$someObject->newMethodCall(false);
CODE_SAMPLE
                ,
                [
                    '$types' => ['SomeClass'],
                    '$oldPropertyName' => 'oldProperty',
                    '$newMethodName' => 'newMethodCall',
                ]
            ),
        ]);
    }

    public function getNodeType(): string
    {
        return Assign::class;
    }

    /**
     * @param Assign $assignNode
     */
    public function refactor(Node $assignNode): ?Node
    {
        if (! $assignNode instanceof Assign) {
            return null;
        }
        if ($this->propertyFetchAnalyzer->isTypesAndProperty(
            $assignNode->var,
            $this->types,
            $this->oldPropertyName
        ) === false) {
            return null;
        }
        /** @var PropertyFetch $propertyFetchNode */
        $propertyFetchNode = $assignNode->var;

        /** @var Variable $propertyNode */
        $propertyNode = $propertyFetchNode->var;

        return $this->methodCallNodeFactory->createWithVariableMethodNameAndArguments(
            $propertyNode,
            $this->newMethodName,
            [$assignNode->expr]
        );
    }
}
