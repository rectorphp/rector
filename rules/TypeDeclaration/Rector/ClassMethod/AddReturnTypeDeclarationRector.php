<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20211020\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector\AddReturnTypeDeclarationRectorTest
 */
final class AddReturnTypeDeclarationRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
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
     * @var \Rector\NodeTypeResolver\TypeComparator\TypeComparator
     */
    private $typeComparator;
    public function __construct(\Rector\NodeTypeResolver\TypeComparator\TypeComparator $typeComparator)
    {
        $this->typeComparator = $typeComparator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        $arrayType = new \PHPStan\Type\ArrayType(new \PHPStan\Type\MixedType(), new \PHPStan\Type\MixedType());
        $configuration = [self::METHOD_RETURN_TYPES => [new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('SomeClass', 'getData', $arrayType)]];
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes defined return typehint of method and class.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function getData()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function getData(): array
    {
    }
}
CODE_SAMPLE
, $configuration)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        foreach ($this->methodReturnTypes as $methodReturnType) {
            if (!$this->isObjectType($node, $methodReturnType->getObjectType())) {
                continue;
            }
            if (!$this->isName($node, $methodReturnType->getMethod())) {
                continue;
            }
            $this->processClassMethodNodeWithTypehints($node, $methodReturnType->getReturnType());
            return $node;
        }
        return null;
    }
    /**
     * @param array<string, AddReturnTypeDeclaration[]> $configuration
     */
    public function configure(array $configuration) : void
    {
        $methodReturnTypes = $configuration[self::METHOD_RETURN_TYPES] ?? [];
        \RectorPrefix20211020\Webmozart\Assert\Assert::allIsInstanceOf($methodReturnTypes, \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration::class);
        $this->methodReturnTypes = $methodReturnTypes;
    }
    private function processClassMethodNodeWithTypehints(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PHPStan\Type\Type $newType) : void
    {
        // remove it
        if ($newType instanceof \PHPStan\Type\MixedType) {
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
        $returnTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($newType, \Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind::RETURN());
        $classMethod->returnType = $returnTypeNode;
    }
}
