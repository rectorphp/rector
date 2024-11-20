<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocParser;

use PhpParser\Node as PhpNode;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use Rector\BetterPhpDocParser\Contract\PhpDocParser\PhpDocNodeDecoratorInterface;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\PhpDocParser\PhpDocParser\PhpDocNodeTraverser;
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
    /**
     * @readonly
     */
    private PhpDocNodeTraverser $phpDocNodeTraverser;
    public function __construct(NameScopeFactory $nameScopeFactory, PhpDocNodeTraverser $phpDocNodeTraverser)
    {
        $this->nameScopeFactory = $nameScopeFactory;
        $this->phpDocNodeTraverser = $phpDocNodeTraverser;
    }
    public function decorate(PhpDocNode $phpDocNode, PhpNode $phpNode) : void
    {
        // iterating all phpdocs has big overhead. peek into the phpdoc to exit early
        if (\strpos($phpDocNode->__toString(), '::') === \false) {
            return;
        }
        $this->phpDocNodeTraverser->traverseWithCallable($phpDocNode, '', function (Node $node) use($phpNode) : ?\PHPStan\PhpDocParser\Ast\Node {
            if (!$node instanceof ArrayItemNode) {
                return null;
            }
            if (!\is_string($node->value)) {
                return null;
            }
            $splitScopeResolution = \explode('::', $node->value);
            if (\count($splitScopeResolution) !== 2) {
                return null;
            }
            $className = $this->resolveFullyQualifiedClass($splitScopeResolution[0], $phpNode);
            $node->setAttribute(PhpDocAttributeKey::RESOLVED_CLASS, $className);
            return $node;
        });
    }
    private function resolveFullyQualifiedClass(string $className, PhpNode $phpNode) : string
    {
        $nameScope = $this->nameScopeFactory->createNameScopeFromNodeWithoutTemplateTypes($phpNode);
        return $nameScope->resolveStringName($className);
    }
}
