<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PHPStanOverride\Analyser;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Analyser\NameScope;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Broker\Broker;
use PHPStan\File\FileHelper;
use PHPStan\Parser\Parser;
use PHPStan\PhpDoc\PhpDocStringResolver;
use PHPStan\PhpDoc\ResolvedPhpDocBlock;
use PHPStan\PhpDoc\Tag\ParamTag;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * This services is not used in Rector directly,
 * but replaces a services in PHPStan container.
 */
final class StandaloneTraitAwarePHPStanNodeScopeResolver extends NodeScopeResolver
{
    /**
     * @var PhpDocStringResolver
     */
    private $phpDocStringResolver;

    /**
     * @param string[][] $earlyTerminatingMethodCalls className(string) => methods(string[])
     */
    public function __construct(
        Broker $broker,
        Parser $parser,
        FileTypeMapper $fileTypeMapper,
        FileHelper $fileHelper,
        TypeSpecifier $typeSpecifier,
        bool $polluteScopeWithLoopInitialAssignments,
        bool $polluteCatchScopeWithTryAssignments,
        bool $polluteScopeWithAlwaysIterableForeach,
        array $earlyTerminatingMethodCalls,
        bool $allowVarTagAboveStatements,
        PhpDocStringResolver $phpDocStringResolver
    ) {
        parent::__construct($broker, $parser, $fileTypeMapper, $fileHelper, $typeSpecifier, $polluteScopeWithLoopInitialAssignments, $polluteCatchScopeWithTryAssignments, $polluteScopeWithAlwaysIterableForeach, $earlyTerminatingMethodCalls, $allowVarTagAboveStatements);

        $this->phpDocStringResolver = $phpDocStringResolver;
    }

    /**
     * @inheritDoc
     */
    public function getPhpDocs(Scope $scope, FunctionLike $functionLike): array
    {
        if (! $this->isClassMethodInTrait($functionLike) || $functionLike->getDocComment() === null) {
            return parent::getPhpDocs($scope, $functionLike);
        }

        // special case for traits
        $phpDocString = $functionLike->getDocComment()->getText();
        $nameScope = $this->createNameScope($functionLike);

        $resolvedPhpDocs = $this->phpDocStringResolver->resolve($phpDocString, $nameScope);

        return $this->convertResolvedPhpDocToArray($resolvedPhpDocs, $functionLike, $scope);
    }

    private function isClassMethodInTrait(FunctionLike $functionLike): bool
    {
        if ($functionLike instanceof Function_) {
            return false;
        }

        $classNode = $functionLike->getAttribute(AttributeKey::CLASS_NODE);

        return $classNode instanceof Trait_;
    }

    private function createNameScope(FunctionLike $functionLike): NameScope
    {
        $namespace = $functionLike->getAttribute(AttributeKey::NAMESPACE_NAME);

        /** @var Node\Stmt\Use_[] $useNodes */
        $useNodes = $functionLike->getAttribute(AttributeKey::USE_NODES) ?? [];
        $uses = [];
        foreach ($useNodes as $useNode) {
            foreach ($useNode->uses as $useUserNode) {
                $useImport = $useUserNode->name->toString();

                $alias = $useUserNode->alias ? (string) $useUserNode->alias : Strings::after($useImport, '\\', -1);

                $phpstanAlias = strtolower($alias);
                $uses[$phpstanAlias] = $useImport;
            }
        }

        $className = $functionLike->getAttribute(AttributeKey::CLASS_NAME);

        return new NameScope($namespace, $uses, $className);
    }

    /**
     * Copy pasted from last part of @see \PHPStan\Analyser\NodeScopeResolver::getPhpDocs()
     */
    private function convertResolvedPhpDocToArray(
        ResolvedPhpDocBlock $resolvedPhpDocBlock,
        FunctionLike $functionLike,
        Scope $scope
    ) {
        $phpDocParameterTypes = $this->resolvePhpDocParameterTypes($resolvedPhpDocBlock);

        $nativeReturnType = $scope->getFunctionType($functionLike->getReturnType(), false, false);

        $phpDocThrowType = $resolvedPhpDocBlock->getThrowsTag() !== null ? $resolvedPhpDocBlock->getThrowsTag()->getType() : null;

        $deprecatedDescription = $resolvedPhpDocBlock->getDeprecatedTag() !== null ? $resolvedPhpDocBlock->getDeprecatedTag()->getMessage() : null;

        return [
            $phpDocParameterTypes,
            $this->resolvePhpDocReturnType($resolvedPhpDocBlock, $nativeReturnType),
            $phpDocThrowType,
            $deprecatedDescription,
            $resolvedPhpDocBlock->isDeprecated(),
            $resolvedPhpDocBlock->isInternal(),
            $resolvedPhpDocBlock->isFinal(),
        ];
    }

    private function resolvePhpDocReturnType(ResolvedPhpDocBlock $resolvedPhpDocBlock, Type $nativeReturnType)
    {
        $phpDocReturnType = null;
        if ($resolvedPhpDocBlock->getReturnTag() !== null && (
            $nativeReturnType->isSuperTypeOf($resolvedPhpDocBlock->getReturnTag()->getType())->yes()
            )) {
            $phpDocReturnType = $resolvedPhpDocBlock->getReturnTag()->getType();
        }
        return $phpDocReturnType;
    }

    private function resolvePhpDocParameterTypes(ResolvedPhpDocBlock $resolvedPhpDocBlock): array
    {
        return array_map(static function (ParamTag $tag): Type {
            return $tag->getType();
        }, $resolvedPhpDocBlock->getParamTags());
    }
}
