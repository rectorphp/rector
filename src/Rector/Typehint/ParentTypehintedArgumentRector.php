<?php declare(strict_types=1);

namespace Rector\Rector\Typehint;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\Php\ParamTypeInfo;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

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
     * @param mixed[] $typehintForArgumentByMethodAndClass
     */
    public function __construct(array $typehintForArgumentByMethodAndClass)
    {
        $this->typehintForArgumentByMethodAndClass = $typehintForArgumentByMethodAndClass;
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
                    'SomeInterface' => [
                        'read' => [
                            '$content' => 'string',
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
            $classNode = $node->getAttribute(Attribute::CLASS_NODE);
            if (! $this->isType($classNode, $type)) {
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
     * @param string[] $parametersToTypes
     */
    private function processClassMethodNodeWithTypehints(
        ClassMethod $classMethodNode,
        array $parametersToTypes
    ): void {
        /** @var Param $param */
        foreach ($classMethodNode->params as $param) {
            foreach ($parametersToTypes as $parameter => $type) {
                $parameter = ltrim($parameter, '$');

                if (! $this->isName($param, $parameter)) {
                    continue;
                }

                if ($type === '') { // remove type
                    $param->type = null;
                } else {
                    $paramTypeInfo = new ParamTypeInfo($parameter, [$type]);
                    $param->type = $paramTypeInfo->getFqnTypeNode();
                }
            }
        }
    }
}
