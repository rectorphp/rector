<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Reflection\ReflectionProvider;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Naming\Naming\UseImportsResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Property\FixClassCaseSensitivityVarDocblockRector\FixClassCaseSensitivityVarDocblockRectorTest
 */
final class FixClassCaseSensitivityVarDocblockRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @readonly
     */
    private UseImportsResolver $useImportsResolver;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, DocBlockUpdater $docBlockUpdater, ReflectionProvider $reflectionProvider, UseImportsResolver $useImportsResolver)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->reflectionProvider = $reflectionProvider;
        $this->useImportsResolver = $useImportsResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Fix a misspelled class name casing in a @var docblock to match the real class name', [new CodeSample(<<<'CODE_SAMPLE'
/** @var AutomailingService */
$service = $this->getService();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
/** @var AutoMailingService */
$service = $this->getService();
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [Property::class, Expression::class];
    }
    /**
     * @param Property|Expression $node
     */
    public function refactor(Node $node): ?Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        $hasChanged = \false;
        foreach ($phpDocInfo->getPhpDocNode()->getVarTagValues() as $varTagValueNode) {
            $correctedTypeNode = $this->correctClassNameCasing($varTagValueNode->type, $node);
            if ($correctedTypeNode instanceof IdentifierTypeNode) {
                $varTagValueNode->type = $correctedTypeNode;
                $hasChanged = \true;
            }
        }
        if (!$hasChanged) {
            return null;
        }
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        return $node;
    }
    private function correctClassNameCasing(TypeNode $typeNode, Node $node): ?IdentifierTypeNode
    {
        if (!$typeNode instanceof IdentifierTypeNode) {
            return null;
        }
        $writtenName = $typeNode->name;
        $existingClassName = $this->resolveExistingClassName($writtenName, $node);
        if ($existingClassName === null) {
            return null;
        }
        $realClassName = $this->reflectionProvider->getClass($existingClassName)->getName();
        $hasLeadingSlash = strncmp($writtenName, '\\', strlen('\\')) === 0;
        $writtenParts = explode('\\', ltrim($writtenName, '\\'));
        $realParts = array_slice(explode('\\', $realClassName), -count($writtenParts));
        // not a pure casing difference, e.g. an alias or partial name
        if (strtolower(implode('\\', $writtenParts)) !== strtolower(implode('\\', $realParts))) {
            return null;
        }
        $correctedName = ($hasLeadingSlash ? '\\' : '') . implode('\\', $realParts);
        if ($correctedName === $writtenName) {
            return null;
        }
        return new IdentifierTypeNode($correctedName);
    }
    /**
     * Resolve the existing class name for a written, possibly miss-cased, identifier.
     * Lookups in PHPStan reflection are case-insensitive, so the namespace must match,
     * while the class name casing may differ.
     */
    private function resolveExistingClassName(string $writtenName, Node $node): ?string
    {
        $bareName = ltrim($writtenName, '\\');
        // already fully qualified
        if (strncmp($writtenName, '\\', strlen('\\')) === 0) {
            return $this->reflectionProvider->hasClass($bareName) ? $bareName : null;
        }
        $parts = explode('\\', $bareName);
        $firstPart = $parts[0];
        // resolve via use imports, matching the imported short name case-insensitively
        foreach ($this->useImportsResolver->resolve() as $use) {
            $prefix = $this->useImportsResolver->resolvePrefix($use);
            foreach ($use->uses as $useItem) {
                if ($useItem->alias instanceof Identifier) {
                    continue;
                }
                if (strtolower($useItem->name->getLast()) !== strtolower($firstPart)) {
                    continue;
                }
                $candidate = $prefix . $useItem->name->toString();
                if (count($parts) > 1) {
                    $candidate .= '\\' . implode('\\', array_slice($parts, 1));
                }
                if ($this->reflectionProvider->hasClass($candidate)) {
                    return $candidate;
                }
            }
        }
        // same namespace
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if ($scope instanceof Scope) {
            $namespace = $scope->getNamespace();
            if ($namespace !== null && $this->reflectionProvider->hasClass($namespace . '\\' . $bareName)) {
                return $namespace . '\\' . $bareName;
            }
        }
        // global namespace
        if ($this->reflectionProvider->hasClass($bareName)) {
            return $bareName;
        }
        return null;
    }
}
