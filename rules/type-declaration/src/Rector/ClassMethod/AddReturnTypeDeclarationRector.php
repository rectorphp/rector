<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\PHPStan\TypeComparator;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddReturnTypeDeclarationRector\AddReturnTypeDeclarationRectorTest
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

    /**
     * @var TypeComparator
     */
    private $typeComparator;

    public function __construct(TypeComparator $typeComparator)
    {
        $this->typeComparator = $typeComparator;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        $arrayType = new ArrayType(new MixedType(), new MixedType());
        $configuration = [
            self::METHOD_RETURN_TYPES => [new AddReturnTypeDeclaration('SomeClass', 'getData', $arrayType)],
        ];

        return new RuleDefinition('Changes defined return typehint of method and class.', [
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
                $configuration
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

    private function processClassMethodNodeWithTypehints(ClassMethod $classMethod, Type $newType): void
    {
        // remove it
        if ($newType instanceof MixedType) {
            $classMethod->returnType = null;
            return;
        }

        // already set → no change
        if ($classMethod->returnType !== null) {
            $currentReturnType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($classMethod->returnType);
            if ($this->typeComparator->areTypesEqual($currentReturnType, $newType)) {
                return;
            }
        }

        $returnTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($newType);
        $classMethod->returnType = $returnTypeNode;
    }
}
