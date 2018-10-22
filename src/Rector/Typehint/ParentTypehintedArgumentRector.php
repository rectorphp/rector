<?php declare(strict_types=1);

namespace Rector\Rector\Typehint;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeTypeResolver\Node\Attribute;
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
     * @param mixed[] $typehintForArgumentByMethodAndClass
     */
    public function __construct(array $typehintForArgumentByMethodAndClass, TypeAnalyzer $typeAnalyzer)
    {
        $this->typehintForArgumentByMethodAndClass = $typehintForArgumentByMethodAndClass;
        $this->typeAnalyzer = $typeAnalyzer;
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

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        /** @var ClassLike $classNode */
        $classNode = $node->getAttribute(Attribute::CLASS_NODE);

        $classNodeTypes = $this->getTypes($classNode);
        if (! $this->isTypeMatch($classNodeTypes)) {
            return null;
        }

        $classNodeTypes = $this->getTypes($node);

        $matchingTypes = $this->getMatchingTypesForClassNode($classNodeTypes);

        /** @var Identifier $identifierNode */
        $identifierNode = $node->name;

        $methodName = $identifierNode->toString();

        foreach ($matchingTypes as $matchingType) {
            $configuration = $this->typehintForArgumentByMethodAndClass[$matchingType];

            foreach ($configuration as $method => $parametersToTypehints) {
                if ($methodName === $method) {
                    return $this->processClassMethodNodeWithTypehints($node, $parametersToTypehints);
                }
            }
        }

        return $node;
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

            $parameterName = $this->getName($variableNode);

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
