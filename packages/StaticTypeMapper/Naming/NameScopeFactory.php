<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\Naming;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PHPStan\Analyser\NameScope;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Naming\Naming\UseImportsResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\StaticTypeMapper;
use RectorPrefix20220531\Symfony\Contracts\Service\Attribute\Required;
/**
 * @see https://github.com/phpstan/phpstan-src/blob/8376548f76e2c845ae047e3010e873015b796818/src/Analyser/NameScope.php#L32
 */
final class NameScopeFactory
{
    /**
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\Naming\Naming\UseImportsResolver
     */
    private $useImportsResolver;
    // This is needed to avoid circular references
    /**
     * @required
     */
    public function autowire(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Naming\Naming\UseImportsResolver $useImportsResolver) : void
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->useImportsResolver = $useImportsResolver;
    }
    public function createNameScopeFromNodeWithoutTemplateTypes(\PhpParser\Node $node) : \PHPStan\Analyser\NameScope
    {
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        $namespace = $scope instanceof \PHPStan\Analyser\Scope ? $scope->getNamespace() : null;
        $uses = $this->useImportsResolver->resolveForNode($node);
        $usesAliasesToNames = $this->resolveUseNamesByAlias($uses);
        if ($scope instanceof \PHPStan\Analyser\Scope && $scope->getClassReflection() instanceof \PHPStan\Reflection\ClassReflection) {
            $classReflection = $scope->getClassReflection();
            $className = $classReflection->getName();
        } else {
            $className = null;
        }
        return new \PHPStan\Analyser\NameScope($namespace, $usesAliasesToNames, $className);
    }
    public function createNameScopeFromNode(\PhpParser\Node $node) : \PHPStan\Analyser\NameScope
    {
        $nameScope = $this->createNameScopeFromNodeWithoutTemplateTypes($node);
        $templateTypeMap = $this->templateTemplateTypeMap($node);
        return new \PHPStan\Analyser\NameScope($nameScope->getNamespace(), $nameScope->getUses(), $nameScope->getClassName(), null, $templateTypeMap);
    }
    /**
     * @param Use_[]|GroupUse[] $useNodes
     * @return array<string, string>
     */
    private function resolveUseNamesByAlias(array $useNodes) : array
    {
        $useNamesByAlias = [];
        foreach ($useNodes as $useNode) {
            $prefix = $useNode instanceof \PhpParser\Node\Stmt\GroupUse ? $useNode->prefix . '\\' : '';
            foreach ($useNode->uses as $useUse) {
                /** @var UseUse $useUse */
                $aliasName = $useUse->getAlias()->name;
                // uses must be lowercase, as PHPStan lowercases it
                $lowercasedAliasName = \strtolower($aliasName);
                $useNamesByAlias[$lowercasedAliasName] = $prefix . $useUse->name->toString();
            }
        }
        return $useNamesByAlias;
    }
    private function templateTemplateTypeMap(\PhpParser\Node $node) : \PHPStan\Type\Generic\TemplateTypeMap
    {
        $nodeTemplateTypes = $this->resolveTemplateTypesFromNode($node);
        $classLike = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\ClassLike::class);
        $classTemplateTypes = [];
        if ($classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            $classTemplateTypes = $this->resolveTemplateTypesFromNode($classLike);
        }
        $templateTypes = \array_merge($nodeTemplateTypes, $classTemplateTypes);
        return new \PHPStan\Type\Generic\TemplateTypeMap($templateTypes);
    }
    /**
     * @return Type[]
     */
    private function resolveTemplateTypesFromNode(\PhpParser\Node $node) : array
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $templateTypes = [];
        foreach ($phpDocInfo->getTemplateTagValueNodes() as $templateTagValueNode) {
            $phpstanType = $this->staticTypeMapper->mapPHPStanPhpDocTypeToPHPStanType($templateTagValueNode, $node);
            $templateTypes[$templateTagValueNode->name] = $phpstanType;
        }
        return $templateTypes;
    }
}
