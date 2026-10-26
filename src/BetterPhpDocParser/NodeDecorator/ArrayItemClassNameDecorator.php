<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\NodeDecorator;

use PhpParser\Node as PhpNode;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\NodeTraverser;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use Rector\BetterPhpDocParser\Contract\PhpDocParser\PhpDocNodeDecoratorInterface;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\PhpDocParser\PhpDocParser\PhpDocNodeVisitor\CallablePhpDocNodeVisitor;
use Rector\StaticTypeMapper\Naming\NameScopeFactory;
/**
 * Decorate node with fully qualified class name for annotation:
 * e.g. @ORM\Column(type=Types::STRING, length=100, nullable=false)
 */
final class ArrayItemClassNameDecorator implements PhpDocNodeDecoratorInterface
{
    /**
     * @readonly
     */
    private NameScopeFactory $nameScopeFactory;
    public function __construct(NameScopeFactory $nameScopeFactory)
    {
        $this->nameScopeFactory = $nameScopeFactory;
    }
    public function decorate(PhpDocNode $phpDocNode, PhpNode $phpNode): void
    {
        // iterating all phpdocs has big overhead. peek into the phpdoc to exit early
        if (strpos($phpDocNode->__toString(), '::') === \false) {
            return;
        }
        $phpDocNodeTraverser = new NodeTraverser([new CallablePhpDocNodeVisitor(function (Node $node) use ($phpNode): ?\PHPStan\PhpDocParser\Ast\Node {
            if (!$node instanceof ArrayItemNode) {
                return null;
            }
            $valueClassName = $this->resolveClassFromScopeResolution($node->value, $phpNode);
            if ($valueClassName !== null) {
                $node->setAttribute(PhpDocAttributeKey::RESOLVED_CLASS, $valueClassName);
            }
            // e.g. @ORM\DiscriminatorMap({ SomeEnum::TOTP = "..." }), the class is in the key
            $keyClassName = $this->resolveClassFromScopeResolution($node->key, $phpNode);
            if ($keyClassName !== null) {
                $node->setAttribute(PhpDocAttributeKey::RESOLVED_KEY_CLASS, $keyClassName);
            }
            return $node;
        })]);
        $phpDocNodeTraverser->traverse([$phpDocNode]);
    }
    /**
     * @param mixed $value
     */
    private function resolveClassFromScopeResolution($value, PhpNode $phpNode): ?string
    {
        if (!is_string($value)) {
            return null;
        }
        $splitScopeResolution = explode('::', $value);
        if (count($splitScopeResolution) !== 2) {
            return null;
        }
        return $this->resolveFullyQualifiedClass($splitScopeResolution[0], $phpNode);
    }
    private function resolveFullyQualifiedClass(string $className, PhpNode $phpNode): string
    {
        $nameScope = $this->nameScopeFactory->createNameScopeFromNodeWithoutTemplateTypes($phpNode);
        return $nameScope->resolveStringName($className);
    }
}
