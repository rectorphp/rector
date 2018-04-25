<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ReturnTypehintRector extends AbstractRector
{
    /**
     * class => [
     *      method => typehting
     * ]
     *
     * @var string[][]
     */
    private $typehintForMethodByClass = [];

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @param mixed[] $typehintForMethodByClass
     */
    public function __construct(array $typehintForMethodByClass, NodeTypeResolver $nodeTypeResolver)
    {
        $this->typehintForMethodByClass = $typehintForMethodByClass;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('[Dynamic] Changes defined return typehint of method and class.', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public getData();
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public getData(): array;
}
CODE_SAMPLE
            ),
        ]);
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof ClassMethod) {
            return false;
        }

        /** @var ClassLike $classNode */
        $classNode = $node->getAttribute(Attribute::CLASS_NODE);
        $classNodeTypes = $this->nodeTypeResolver->resolve($classNode);
        if (! $classNodeTypes) {
            return false;
        }

        return $this->isTypeMatch($classNodeTypes);
    }

    /**
     * @param ClassMethod $classMethodNode
     */
    public function refactor(Node $classMethodNode): ?Node
    {
        /** @var Class_ $classMethodNode */
        $classNode = $classMethodNode->getAttribute(Attribute::CLASS_NODE);
        $classNodeTypes = $this->nodeTypeResolver->resolve($classNode);

        $matchingTypes = $this->getMatchingTypesForClassNode($classNodeTypes);

        /** @var Identifier $identifierNode */
        $identifierNode = $classMethodNode->name;

        $methodName = $identifierNode->toString();

        foreach ($matchingTypes as $matchingType) {
            $configuration = $this->typehintForMethodByClass[$matchingType];

            foreach ($configuration as $method => $methodReturnTypehint) {
                if ($methodName === $method) {
                    return $this->processClassMethodNodeWithTypehints($classMethodNode, $methodReturnTypehint);
                }
            }
        }

        return $classMethodNode;
    }

    /**
     * @return string[]
     */
    private function getClasses(): array
    {
        return array_keys($this->typehintForMethodByClass);
    }

    /**
     * @param string[] $types
     */
    private function isTypeMatch(array $types): bool
    {
        return (bool) $this->getMatchingTypesForClassNode($types);
    }

    /**
     * @param string[] $types
     * @return string[]
     */
    private function getMatchingTypesForClassNode(array $types): array
    {
        return array_intersect($types, $this->getClasses());
    }

    private function processClassMethodNodeWithTypehints(
        ClassMethod $classMethodNode,
        string $methodReturnTypehint
    ): ClassMethod {
        // already set
        if ($classMethodNode->returnType && $classMethodNode->returnType->name === $methodReturnTypehint) {
            return $classMethodNode;
        }

        $classMethodNode->setAttribute(Attribute::ORIGINAL_NODE, null);
        $classMethodNode->returnType = new Identifier($methodReturnTypehint);

        return $classMethodNode;
    }
}
