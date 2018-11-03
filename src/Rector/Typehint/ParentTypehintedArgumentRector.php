<?php declare(strict_types=1);

namespace Rector\Rector\Typehint;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
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
        foreach ($this->typehintForArgumentByMethodAndClass as $type => $methodToArgumentToTypes) {
            if (! $this->isType($node, $type)) {
                continue;
            }

            foreach ($methodToArgumentToTypes as $method => $argumentToTypes) {
                if (! $this->isName($node, $method)) {
                    continue;
                }

                $this->processClassMethodNodeWithTypehints($node, $argumentToTypes);
                continue 2;
            }
        }

        return null;
    }

    /**
     * @param string[] $parametersToTypehints
     */
    private function processClassMethodNodeWithTypehints(
        ClassMethod $classMethodNode,
        array $parametersToTypehints
    ): void {
        /** @var Param $param */
        foreach ($classMethodNode->params as $param) {
            foreach ($parametersToTypehints as $parameter => $typehint) {
                if (! $this->isName($param, $parameter)) {
                    continue;
                }

                if ($this->typeAnalyzer->isBuiltinType($typehint)) {
                    $param->type = BuilderHelpers::normalizeType($typehint);
                } else {
                    $param->type = new FullyQualified($typehint);
                }
            }
        }
    }
}
