<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Generic\ValueObject\AddReturnTypeDeclaration;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Generic\Tests\Rector\ClassMethod\AddReturnTypeDeclarationRector\AddReturnTypeDeclarationRectorTest
 */
final class AddReturnTypeDeclarationRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const METHOD_RETURN_TYPES = 'method_return_types';

    /**
     * @var AddReturnTypeDeclaration[]
     */
    private $methodReturnTypes = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes defined return typehint of method and class.', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public getData()
    {
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public getData(): array
    {
    }
}
CODE_SAMPLE
                ,
                [
                    self::METHOD_RETURN_TYPES => [new AddReturnTypeDeclaration('SomeClass', 'getData', 'array')],
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
        foreach ($this->methodReturnTypes as $methodReturnType) {
            if (! $this->isObjectType($node, $methodReturnType->getClass())) {
                continue;
            }

            if (! $this->isName($node, $methodReturnType->getMethod())) {
                continue;
            }

            $this->processClassMethodNodeWithTypehints($node, $methodReturnType->getReturnType());

            return $node;
        }

        return null;
    }

    public function configure(array $configuration): void
    {
        $methodReturnTypes = $configuration[self::METHOD_RETURN_TYPES] ?? [];
        Assert::allIsInstanceOf($methodReturnTypes, AddReturnTypeDeclaration::class);

        $this->methodReturnTypes = $methodReturnTypes;
    }

    private function processClassMethodNodeWithTypehints(ClassMethod $classMethod, string $newType): void
    {
        // remove it
        if ($newType === '') {
            $classMethod->returnType = null;
            return;
        }

        // already set → no change
        if ($classMethod->returnType && $this->isName($classMethod->returnType, $newType)) {
            return;
        }

        $returnTypeNode = $this->staticTypeMapper->mapStringToPhpParserNode($newType);
        $classMethod->returnType = $returnTypeNode;
    }
}
