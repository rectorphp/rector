<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\NodeFinder;

use PhpParser\Node\Attribute;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\TypeDeclaration\ValueObject\DataProviderNodes;
use Rector\TypeDeclarationDocblocks\Enum\TestClassName;
final class DataProviderMethodsFinder
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, NodeNameResolver $nodeNameResolver)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return ClassMethod[]
     */
    public function findDataProviderNodesInClass(Class_ $class): array
    {
        $dataProviderClassMethods = [];
        foreach ($class->getMethods() as $classMethod) {
            $currentDataProviderNodes = $this->findDataProviderNodes($class, $classMethod);
            $dataProviderClassMethods = array_merge($dataProviderClassMethods, $currentDataProviderNodes->getClassMethods());
        }
        return $dataProviderClassMethods;
    }
    public function findDataProviderNodes(Class_ $class, ClassMethod $classMethod): DataProviderNodes
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
        if ($phpDocInfo instanceof PhpDocInfo) {
            $phpdocNodes = $phpDocInfo->getTagsByName('@dataProvider');
        } else {
            $phpdocNodes = [];
        }
        $attributes = $this->findDataProviderAttributes($classMethod);
        return new DataProviderNodes($class, $attributes, $phpdocNodes);
    }
    /**
     * @return array<Attribute>
     */
    private function findDataProviderAttributes(ClassMethod $classMethod): array
    {
        $dataProviders = [];
        foreach ($classMethod->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {
                if (!$this->nodeNameResolver->isName($attribute->name, TestClassName::DATA_PROVIDER)) {
                    continue;
                }
                $dataProviders[] = $attribute;
            }
        }
        return $dataProviders;
    }
}
