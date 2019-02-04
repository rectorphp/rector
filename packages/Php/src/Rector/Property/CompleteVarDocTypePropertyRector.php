<?php declare(strict_types=1);

namespace Rector\Php\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Rector\NodeTypeResolver\ComplexNodeTypeResolver;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockAnalyzer;
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
     * @var ComplexNodeTypeResolver
     */
    private $complexNodeTypeResolver;

    public function __construct(DocBlockAnalyzer $docBlockAnalyzer, ComplexNodeTypeResolver $complexNodeTypeResolver)
    {
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->complexNodeTypeResolver = $complexNodeTypeResolver;
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

        $varTypeInfo = $this->complexNodeTypeResolver->resolvePropertyTypeInfo($node);
        if ($varTypeInfo === null) {
            return null;
        }

        if ($varTypeInfo->getDocTypes() === []) {
            return null;
        }

        $varType = implode('|', $varTypeInfo->getDocTypes());

        $this->docBlockAnalyzer->addVarTag($node, $varType);

        $node->setAttribute(Attribute::ORIGINAL_NODE, null);

        return $node;
    }
}
