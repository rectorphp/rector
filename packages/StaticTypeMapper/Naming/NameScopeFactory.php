<?php

declare(strict_types=1);

namespace Rector\StaticTypeMapper\Naming;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PHPStan\Analyser\NameScope;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @see https://github.com/phpstan/phpstan-src/blob/8376548f76e2c845ae047e3010e873015b796818/src/Analyser/NameScope.php#L32
 */
final class NameScopeFactory
{
    private StaticTypeMapper $staticTypeMapper;

    private PhpDocInfoFactory $phpDocInfoFactory;

    private BetterNodeFinder $betterNodeFinder;

    // This is needed to avoid circular references

    #[Required]
    public function autowire(
        PhpDocInfoFactory $phpDocInfoFactory,
        StaticTypeMapper $staticTypeMapper,
        BetterNodeFinder $betterNodeFinder,
    ): void {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->betterNodeFinder = $betterNodeFinder;
    }

    public function createNameScopeFromNodeWithoutTemplateTypes(Node $node): NameScope
    {
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        $namespace = $scope instanceof Scope ? $scope->getNamespace() : null;

        /** @var Use_[] $useNodes */
        $useNodes = (array) $node->getAttribute(AttributeKey::USE_NODES);
        $uses = $this->resolveUseNamesByAlias($useNodes);

        if ($scope instanceof Scope && $scope->getClassReflection() instanceof ClassReflection) {
            $classReflection = $scope->getClassReflection();
            $className = $classReflection->getName();
        } else {
            $className = null;
        }

        return new NameScope($namespace, $uses, $className);
    }

    public function createNameScopeFromNode(Node $node): NameScope
    {
        $nameScope = $this->createNameScopeFromNodeWithoutTemplateTypes($node);
        $templateTypeMap = $this->templateTemplateTypeMap($node);

        return new NameScope(
            $nameScope->getNamespace(),
            $nameScope->getUses(),
            $nameScope->getClassName(),
            null,
            $templateTypeMap
        );
    }

//    public function setStaticTypeMapper(StaticTypeMapper $staticTypeMapper): void
//    {
//        $this->staticTypeMapper = $staticTypeMapper;
//    }

    /**
     * @param Use_[] $useNodes
     * @return array<string, string>
     */
    private function resolveUseNamesByAlias(array $useNodes): array
    {
        $useNamesByAlias = [];

        foreach ($useNodes as $useNode) {
            foreach ($useNode->uses as $useUse) {
                /** @var UseUse $useUse */
                $aliasName = $useUse->getAlias()
                    ->name;

                // uses must be lowercase, as PHPStan lowercases it
                $lowercasedAliasName = strtolower($aliasName);

                $useNamesByAlias[$lowercasedAliasName] = $useUse->name->toString();
            }
        }

        return $useNamesByAlias;
    }

    private function templateTemplateTypeMap(Node $node): TemplateTypeMap
    {
        $nodeTemplateTypes = $this->resolveTemplateTypesFromNode($node);

        $classLike = $this->betterNodeFinder->findParentType($node, ClassLike::class);

        $classTemplateTypes = [];
        if ($classLike instanceof ClassLike) {
            $classTemplateTypes = $this->resolveTemplateTypesFromNode($classLike);
        }

        $templateTypes = array_merge($nodeTemplateTypes, $classTemplateTypes);
        return new TemplateTypeMap($templateTypes);
    }

    /**
     * @return Type[]
     */
    private function resolveTemplateTypesFromNode(Node $node): array
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
