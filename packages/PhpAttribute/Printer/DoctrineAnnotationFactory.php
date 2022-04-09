<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\Printer;

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
    public function __construct(\Rector\Core\Contract\PhpParser\NodePrinterInterface $nodePrinter)
    {
        $this->nodePrinter = $nodePrinter;
    }
    public function createFromAttribute(\PhpParser\Node\Attribute $attribute, string $className) : \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode
    {
        $items = $this->createItemsFromArgs($attribute->args);
        $identifierTypeNode = new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode($className);
        return new \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode($identifierTypeNode, null, $items);
    }
    /**
     * @param Arg[] $args
     * @return mixed[]
     */
    private function createItemsFromArgs(array $args) : array
    {
        $items = [];
        foreach ($args as $arg) {
            if ($arg->value instanceof \PhpParser\Node\Scalar\String_) {
                // standardize double quotes for annotations
                $arg->value->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::KIND, \PhpParser\Node\Scalar\String_::KIND_DOUBLE_QUOTED);
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
