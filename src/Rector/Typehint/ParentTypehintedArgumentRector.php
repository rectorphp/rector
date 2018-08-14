<?php declare(strict_types=1);

namespace Rector\Rector\Typehint;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\Utils\Php\TypeAnalyzer;

final class ParentTypehintedArgumentRector extends AbstractRector
{
    /**
     * class => [
     *      method => [
     *           argument => typehting
     *      ]
     * ]
     *
     * @var string[][][]
     */
    private $typehintForArgumentByMethodAndClass = [];

    /**
     * @var TypeAnalyzer
     */
    private $typeAnalyzer;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @param mixed[] $typehintForArgumentByMethodAndClass
     */
    public function __construct(
        array $typehintForArgumentByMethodAndClass,
        TypeAnalyzer $typeAnalyzer,
        NodeTypeResolver $nodeTypeResolver
    ) {
        $this->typehintForArgumentByMethodAndClass = $typehintForArgumentByMethodAndClass;
        $this->typeAnalyzer = $typeAnalyzer;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes defined parent class typehints.', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
interface SomeInterface
{
    public read(string $content);
}

class SomeClass implements SomeInterface
{
    public read($content);
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
interface SomeInterface
{
    public read(string $content);
}

class SomeClass implements SomeInterface
{
    public read(string $content);
}
CODE_SAMPLE
                ,
                [
                    '$typehintForArgumentByMethodAndClass' => [
                        'SomeInterface' => [
                            'read' => [
                                '$content' => 'string',
                            ],
                        ],
                    ],
                ]
            ),
        ]);
    }

    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    /**
     * @param ClassMethod $classMethodNode
     */
    public function refactor(Node $classMethodNode): ?Node
    {
        if (! $classMethodNode instanceof ClassMethod) {
            return null;
        }
        /** @var ClassLike $classNode */
        $classNode = $classMethodNode->getAttribute(Attribute::CLASS_NODE);
        $classNodeTypes = $this->nodeTypeResolver->resolve($classNode);
        if ($this->isTypeMatch($classNodeTypes) === false) {
            return null;
        }
        /** @var Class_ $classMethodNode */
        $classNode = $classMethodNode->getAttribute(Attribute::CLASS_NODE);
        $classNodeTypes = $this->nodeTypeResolver->resolve($classNode);

        $matchingTypes = $this->getMatchingTypesForClassNode($classNodeTypes);

        /** @var Identifier $identifierNode */
        $identifierNode = $classMethodNode->name;

        $methodName = $identifierNode->toString();

        foreach ($matchingTypes as $matchingType) {
            $configuration = $this->typehintForArgumentByMethodAndClass[$matchingType];

            foreach ($configuration as $method => $parametersToTypehints) {
                if ($methodName === $method) {
                    return $this->processClassMethodNodeWithTypehints($classMethodNode, $parametersToTypehints);
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
        return array_keys($this->typehintForArgumentByMethodAndClass);
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

    /**
     * @param string[] $parametersToTypehints
     */
    private function processClassMethodNodeWithTypehints(
        ClassMethod $classMethodNode,
        array $parametersToTypehints
    ): ClassMethod {
        /** @var Param $param */
        foreach ($classMethodNode->params as $param) {
            /** @var Variable $variableNode */
            $variableNode = $param->var;

            $parameterName = (string) $variableNode->name;

            if (! isset($parametersToTypehints[$parameterName])) {
                continue;
            }

            $newTypehint = $parametersToTypehints[$parameterName];

            if ($this->typeAnalyzer->isBuiltinType($newTypehint)) {
                $param->type = BuilderHelpers::normalizeType($newTypehint);
            } else {
                $param->type = new FullyQualified($newTypehint);
            }
        }

        return $classMethodNode;
    }
}
