<?php

declare(strict_types=1);

namespace Rector\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Tests\Rector\ClassMethod\AddReturnTypeDeclarationRector\AddReturnTypeDeclarationRectorTest
 */
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
    public function __construct(array $typehintForMethodByClass = [])
    {
        dump($typehintForMethodByClass);

        $this->typehintForMethodByClass = $typehintForMethodByClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes defined return typehint of method and class.', [
            new ConfiguredCodeSample(
                <<<'PHP'
class SomeClass
{
    public getData()
    {
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public getData(): array
    {
    }
}
PHP
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
            if (! $this->isObjectType($node, $type)) {
                continue;
            }

            foreach ($methodsToTypehints as $method => $typehint) {
                if (! $this->isName($node, $method)) {
                    continue;
                }

                $this->processClassMethodNodeWithTypehints($node, $typehint);

                return $node;
            }
        }

        return null;
    }

    private function processClassMethodNodeWithTypehints(ClassMethod $classMethod, string $newType): void
    {
        // already set → no change
        if ($classMethod->returnType && $this->isName($classMethod->returnType, $newType)) {
            return;
        }

        // remove it
        if ($newType === '') {
            $classMethod->returnType = null;
            return;
        }

        $returnTypeNode = $this->staticTypeMapper->mapStringToPhpParserNode($newType);
        if ($returnTypeNode === null) {
            return;
        }

        $classMethod->returnType = $returnTypeNode;
    }
}
