<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocParser;

use PhpParser\Node as PhpNode;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\Contract\PhpDocParser\PhpDocNodeDecoratorInterface;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\PhpDocParser\PhpDocParser\PhpDocNodeTraverser;
use Rector\StaticTypeMapper\Naming\NameScopeFactory;
/**
 * Decorate node with fully qualified class name for generic annotations for @uses, @used-by, and @see
 * e.g. @uses Direction::*
 *
 * @see https://docs.phpdoc.org/guide/references/phpdoc/tags/uses.html
 */
final class PhpDocTagGenericUsesDecorator implements PhpDocNodeDecoratorInterface
{
    /**
     * @readonly
     */
    private NameScopeFactory $nameScopeFactory;
    /**
     * @readonly
     */
    private PhpDocNodeTraverser $phpDocNodeTraverser;
    public function __construct(NameScopeFactory $nameScopeFactory, PhpDocNodeTraverser $phpDocNodeTraverser)
    {
        $this->nameScopeFactory = $nameScopeFactory;
        $this->phpDocNodeTraverser = $phpDocNodeTraverser;
    }
    public function decorate(PhpDocNode $phpDocNode, PhpNode $phpNode): void
    {
        // iterating all phpdocs has big overhead. peek into the phpdoc to exit early
        if (strpos($phpDocNode->__toString(), '::') === \false) {
            return;
        }
        $this->phpDocNodeTraverser->traverseWithCallable($phpDocNode, '', function (Node $node) use ($phpNode): ?\PHPStan\PhpDocParser\Ast\Node {
            if (!$node instanceof PhpDocTagNode) {
                return null;
            }
            if (!$node->value instanceof GenericTagValueNode) {
                return null;
            }
            if (!in_array($node->name, ['@uses', '@used-by', '@see'], \true)) {
                return null;
            }
            $reference = $node->value->value;
            if (strpos($reference, '::') === \false) {
                return null;
            }
            if ($node->value->hasAttribute(PhpDocAttributeKey::RESOLVED_CLASS)) {
                return null;
            }
            $classValue = explode('::', $reference)[0];
            $className = $this->resolveFullyQualifiedClass($classValue, $phpNode);
            $node->value->setAttribute(PhpDocAttributeKey::RESOLVED_CLASS, $className);
            return $node;
        });
    }
    private function resolveFullyQualifiedClass(string $classValue, PhpNode $phpNode): string
    {
        $nameScope = $this->nameScopeFactory->createNameScopeFromNodeWithoutTemplateTypes($phpNode);
        return $nameScope->resolveStringName($classValue);
    }
}
