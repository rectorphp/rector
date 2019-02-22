<?php declare(strict_types=1);

namespace Rector\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeTypeResolver\Php\ReturnTypeInfo;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class AddReturnTypeDeclarationRector extends AbstractRector
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
     * @param mixed[] $typehintForMethodByClass
     */
    public function __construct(array $typehintForMethodByClass)
    {
        $this->typehintForMethodByClass = $typehintForMethodByClass;
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
                    'SomeClass' => [
                        'getData' => 'array',
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

    private function processClassMethodNodeWithTypehints(ClassMethod $classMethod, string $newType): ?ClassMethod
    {
        // already set → no change
        if ($classMethod->returnType && $this->isName($classMethod->returnType, $newType)) {
            return null;
        }

        // remove it
        if ($newType === '') {
            $classMethod->returnType = null;
        } else {
            $returnTypeInfo = new ReturnTypeInfo([$newType]);
            $classMethod->returnType = $returnTypeInfo->getFqnTypeNode();
        }

        return $classMethod;
    }
}
