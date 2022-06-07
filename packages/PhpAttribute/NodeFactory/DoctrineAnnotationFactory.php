<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\Scalar\String_;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\Core\Contract\PhpParser\NodePrinterInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class DoctrineAnnotationFactory
{
    /**
     * @readonly
     * @var \Rector\Core\Contract\PhpParser\NodePrinterInterface
     */
    private $nodePrinter;
    public function __construct(NodePrinterInterface $nodePrinter)
    {
        $this->nodePrinter = $nodePrinter;
    }
    public function createFromAttribute(Attribute $attribute, string $className) : DoctrineAnnotationTagValueNode
    {
        $items = $this->createItemsFromArgs($attribute->args);
        $identifierTypeNode = new IdentifierTypeNode($className);
        return new DoctrineAnnotationTagValueNode($identifierTypeNode, null, $items);
    }
    /**
     * @param Arg[] $args
     * @return mixed[]
     */
    private function createItemsFromArgs(array $args) : array
    {
        $items = [];
        foreach ($args as $arg) {
            if ($arg->value instanceof String_) {
                // standardize double quotes for annotations
                $arg->value->setAttribute(AttributeKey::KIND, String_::KIND_DOUBLE_QUOTED);
            }
            $itemValue = $this->nodePrinter->print($arg->value);
            if ($arg->name !== null) {
                $name = $this->nodePrinter->print($arg->name);
                $items[$name] = $itemValue;
            } else {
                $items[] = $itemValue;
            }
        }
        return $items;
    }
}
