<?php declare(strict_types=1);

namespace Rector\Rector\Typehint;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Php\TypeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
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
     * @var TypeAnalyzer
     */
    private $typeAnalyzer;

    /**
     * @param mixed[] $typehintForMethodByClass
     */
    public function __construct(array $typehintForMethodByClass, TypeAnalyzer $typeAnalyzer)
    {
        $this->typehintForMethodByClass = $typehintForMethodByClass;
        $this->typeAnalyzer = $typeAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes defined return typehint of method and class.', [
            new ConfiguredCodeSample(
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
                ,
                [
                    '$typehintForMethodByClass' => [
                        'SomeClass' => [
                            'getData' => 'array',
                        ],
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $classMethodNode
     */
    public function refactor(Node $classMethodNode): ?Node
    {
        /** @var ClassLike $classNode */
        $classNode = $classMethodNode->getAttribute(Attribute::CLASS_NODE);
        $classNodeTypes = $this->nodeTypeResolver->resolve($classNode);
        if (! $classNodeTypes) {
            return null;
        }
        if ($this->isTypeMatch($classNodeTypes) === false) {
            return null;
        }
        /** @var Class_ $classMethodNode */
        $classNode = $classMethodNode->getAttribute(Attribute::CLASS_NODE);
        $classNodeTypes = $this->getTypes($classNode);

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
        string $newTypehint
    ): ClassMethod {
        // already set
        if ($classMethodNode->returnType && $classMethodNode->returnType->name === $newTypehint) {
            return $classMethodNode;
        }

        // remote it
        if ($newTypehint === '') {
            $classMethodNode->returnType = null;
            return $classMethodNode;
        }

        // @todo possibly decouple to smth like IdentifierRenamer?
        if ($this->typeAnalyzer->isPhpReservedType($newTypehint)) {
            $classMethodNode->returnType = new Identifier($newTypehint);
        } elseif ($this->typeAnalyzer->isNullableType($newTypehint)) {
            $classMethodNode->returnType = new NullableType('\\' . ltrim($newTypehint, '?'));
        } else {
            $classMethodNode->returnType = new FullyQualified($newTypehint);
        }

        return $classMethodNode;
    }
}
