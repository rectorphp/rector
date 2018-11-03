<?php declare(strict_types=1);

namespace Rector\Rector\Typehint;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
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
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->typehintForMethodByClass as $type => $methodsToTypehints) {
            if (! $this->isType($node, $type)) {
                continue;
            }

            foreach ($methodsToTypehints as $method => $typehint) {
                if (! $this->isName($node, $method)) {
                    continue;
                }

                return $this->processClassMethodNodeWithTypehints($node, $typehint);
            }
        }

        return null;
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
