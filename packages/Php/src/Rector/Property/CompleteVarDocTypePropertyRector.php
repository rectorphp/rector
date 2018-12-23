<?php declare(strict_types=1);

namespace Rector\Php\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\Node\NodeToStringTypeResolver;
use Rector\NodeTypeResolver\NodeTypeAnalyzer;
use Rector\NodeTypeResolver\Php\VarTypeInfo;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockAnalyzer;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class CompleteVarDocTypePropertyRector extends AbstractRector
{
    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var NodeTypeAnalyzer
     */
    private $nodeTypeAnalyzer;

    /**
     * @var NodeToStringTypeResolver
     */
    private $nodeToStringTypeResolver;

    public function __construct(
        DocBlockAnalyzer $docBlockAnalyzer,
        BetterNodeFinder $betterNodeFinder,
        NodeTypeAnalyzer $nodeTypeAnalyzer,
        NodeToStringTypeResolver $nodeToStringTypeResolver
    ) {
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeTypeAnalyzer = $nodeTypeAnalyzer;
        $this->nodeToStringTypeResolver = $nodeToStringTypeResolver;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Complete property `@var` annotations for missing one, yet known.', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    private $eventDispatcher;

    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Property::class];
    }

    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?Node
    {
        $varTypeInfo = $this->docBlockAnalyzer->getVarTypeInfo($node);
        if ($varTypeInfo) {
            return null;
        }

        $varTypeInfo = $this->resolveStaticVarTypeInfo($node);
        if ($varTypeInfo === null) {
            return null;
        }

        if ($varTypeInfo->getType() === null) {
            return null;
        }

        $varType = implode('|', $varTypeInfo->getTypes());
        $this->docBlockAnalyzer->addVarTag($node, $varType);

        $node->setAttribute(Attribute::ORIGINAL_NODE, null);

        return $node;
    }

    /**
     * @todo extract
     * Based on static analysis of code, looking for property assigns
     */
    private function resolveStaticVarTypeInfo(Property $propertyNode): ?VarTypeInfo
    {
        $types = [];

        $propertyDefault = $propertyNode->props[0]->default;
        if ($propertyDefault) {
            $types[] = $this->nodeToStringTypeResolver->resolver($propertyDefault);
        }

        /** @var Class_ $classNode */
        $classNode = $propertyNode->getAttribute(Attribute::CLASS_NODE);

        $propertyName = $this->getName($propertyNode);

        /** @var Assign[] $propertyAssignNodes */
        $propertyAssignNodes = $this->betterNodeFinder->find([$classNode], function (Node $node) use (
            $propertyName
        ): bool {
            if ($node instanceof Assign) {
                if ($node->var instanceof PropertyFetch) {
                    // is property match
                    return $this->isName($node->var, $propertyName);
                }
            }

            return false;
        });

        foreach ($propertyAssignNodes as $propertyAssignNode) {
            $types = array_merge(
                $types,
                $this->nodeTypeAnalyzer->resolveSingleTypeToStrings($propertyAssignNode->expr)
            );
        }

        $types = array_filter($types);

        return new VarTypeInfo($types);
    }
}
