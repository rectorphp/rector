<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ResourceType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202411\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddTypeFromResourceDocblockRector\AddTypeFromResourceDocblockRectorTest
 */
final class AddTypeFromResourceDocblockRector extends AbstractRector implements MinPhpVersionInterface, ConfigurableRectorInterface
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\Comments\NodeDocBlock\DocBlockUpdater
     */
    private $docBlockUpdater;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    /**
     * @var string
     */
    private $newTypeFromResourceDoc;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, PhpDocTypeChanger $phpDocTypeChanger, DocBlockUpdater $docBlockUpdater, StaticTypeMapper $staticTypeMapper, PhpDocTagRemover $phpDocTagRemover)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->phpDocTagRemover = $phpDocTagRemover;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add param and return types on resource docblock', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @param resource|null $resource
     */
    public function setResource($resource)
    {
    }

    /**
     * @return resource|null
     */
    public function getResource()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function setResource(?App\ValueObject\Resource $resource)
    {
    }

    public function getResource(): ?App\ValueObject\Resource
    {
    }
}
CODE_SAMPLE
, ['App\\ValueObject\\Resource'])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassMethod::class];
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::NULLABLE_TYPE;
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        $hasChanged = \false;
        $phpdocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpdocInfo instanceof PhpDocInfo) {
            return null;
        }
        // for return type
        if (!$node->returnType instanceof Node) {
            $returnType = $phpdocInfo->getReturnType();
            $newType = $this->resolveNewType($returnType);
            if ($newType instanceof Type) {
                $returnTagValueNode = $phpdocInfo->getReturnTagValue();
                if ($returnTagValueNode instanceof ReturnTagValueNode) {
                    if ($returnTagValueNode->description !== '') {
                        $this->phpDocTypeChanger->changeReturnType($node, $phpdocInfo, $newType);
                    } else {
                        $phpdocInfo->removeByType(ReturnTagValueNode::class);
                        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
                    }
                    $node->returnType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($newType, TypeKind::RETURN);
                    $hasChanged = \true;
                }
            }
        }
        // for param type
        foreach ($node->params as $param) {
            if ($param->type instanceof Node) {
                continue;
            }
            $paramName = $this->getName($param);
            $paramType = $phpdocInfo->getParamType($this->getName($param));
            $newType = $this->resolveNewType($paramType);
            if ($newType instanceof Type) {
                $paramTagValueByName = $phpdocInfo->getParamTagValueByName($paramName);
                if (!$paramTagValueByName instanceof ParamTagValueNode) {
                    continue;
                }
                if ($paramTagValueByName->description !== '') {
                    $this->phpDocTypeChanger->changeParamType($node, $phpdocInfo, $newType, $param, $paramName);
                } else {
                    $this->phpDocTagRemover->removeTagValueFromNode($phpdocInfo, $paramTagValueByName);
                    $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
                }
                $param->type = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($newType, TypeKind::RETURN);
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::stringNotEmpty(\current($configuration));
        $this->newTypeFromResourceDoc = \current($configuration);
    }
    private function resolveNewType(Type $type) : ?Type
    {
        $newType = null;
        if ($type instanceof UnionType) {
            $types = $type->getTypes();
            foreach ($types as $key => $type) {
                if ($type instanceof ResourceType) {
                    $types[$key] = new ObjectType($this->newTypeFromResourceDoc);
                    $newType = new UnionType($types);
                    break;
                }
            }
        } elseif ($type instanceof ResourceType) {
            $newType = new ObjectType($this->newTypeFromResourceDoc);
        }
        return $newType;
    }
}
