<?php declare(strict_types=1);

namespace Rector\DomainDrivenDesign\Rector\ObjectToScalar;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Property;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\Node\CurrentNodeProvider;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ObjectToScalarDocBlockRector extends AbstractObjectToScalarRector
{
    /**
     * @var CurrentNodeProvider
     */
    private $currentNodeProvider;

    /**
     * @param string[] $valueObjectsToSimpleTypes
     */
    public function __construct(array $valueObjectsToSimpleTypes, CurrentNodeProvider $currentNodeProvider)
    {
        $this->currentNodeProvider = $currentNodeProvider;
        parent::__construct($valueObjectsToSimpleTypes);
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns defined value object to simple types in doc blocks', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
/**
 * @var ValueObject|null
 */
private $name;

/** @var ValueObject|null */
$name;
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
/**
 * @var string|null
 */
private $name;

/** @var string|null */
$name;
CODE_SAMPLE
                ,
                [
                    '$valueObjectsToSimpleTypes' => [
                        'ValueObject' => 'string',
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Property::class, NullableType::class, Variable::class];
    }

    /**
     * @param Property|NullableType|Variable $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Property && $this->isTypes($node, array_keys($this->valueObjectsToSimpleTypes))) {
            $this->refactorProperty($node);
            return $node;
        }

        if ($node instanceof NullableType) {
            $this->refactorNullableType($node);
            return $node;
        }

        if ($node instanceof Variable) {
            $this->refactorVariableNode($node);
            return $node;
        }

        return null;
    }

    private function refactorProperty(Property $property): void
    {
        $match = $this->matchOriginAndNewType($property);
        if ($match === null) {
            return;
        }

        [$oldType, $newType] = $match;

        $this->docBlockAnalyzer->changeType($property, $oldType, $newType);
    }

    private function refactorNullableType(NullableType $nullableType): void
    {
        $newType = $this->matchNewType($nullableType->type);
        if ($newType === null) {
            return;
        }

        // in method parameter update docs as well
        $parentNode = $nullableType->getAttribute(Attribute::PARENT_NODE);
        if ($parentNode instanceof Param) {
            $this->processParamNode($nullableType, $parentNode, $newType);
        }
    }

    private function refactorVariableNode(Variable $variable): void
    {
        $match = $this->matchOriginAndNewType($variable);
        if (! $match) {
            return;
        }

        [$oldType, $newType] = $match;

        $exprNode = $this->betterNodeFinder->findFirstAncestorInstanceOf($variable, Expr::class);
        $node = $variable;
        if ($exprNode && $exprNode->getAttribute(Attribute::PARENT_NODE)) {
            $node = $exprNode->getAttribute(Attribute::PARENT_NODE);
        }

        if ($node === null) {
            return;
        }

        $this->docBlockAnalyzer->changeType($node, $oldType, $newType);
    }

    private function processParamNode(NullableType $nullableType, Param $param, string $newType): void
    {
        $classMethodNode = $param->getAttribute(Attribute::PARENT_NODE);
        if ($classMethodNode === null) {
            return;
        }

        $this->currentNodeProvider->setNode($nullableType);

        $oldType = $this->namespaceAnalyzer->resolveTypeToFullyQualified((string) $nullableType->type);

        $this->docBlockAnalyzer->changeType($classMethodNode, $oldType, $newType);
    }
}
