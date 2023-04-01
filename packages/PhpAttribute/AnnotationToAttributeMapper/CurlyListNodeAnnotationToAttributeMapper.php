<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\AnnotationToAttributeMapper;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\LNumber;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\PhpAttribute\AnnotationToAttributeMapper;
use Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;
use Rector\PhpAttribute\Enum\DocTagNodeState;
use RectorPrefix202304\Symfony\Contracts\Service\Attribute\Required;
use RectorPrefix202304\Webmozart\Assert\Assert;
/**
 * @implements AnnotationToAttributeMapperInterface<CurlyListNode>
 */
final class CurlyListNodeAnnotationToAttributeMapper implements AnnotationToAttributeMapperInterface
{
    /**
     * @var \Rector\PhpAttribute\AnnotationToAttributeMapper
     */
    private $annotationToAttributeMapper;
    /**
     * Avoid circular reference
     * @required
     */
    public function autowire(AnnotationToAttributeMapper $annotationToAttributeMapper) : void
    {
        $this->annotationToAttributeMapper = $annotationToAttributeMapper;
    }
    /**
     * @param mixed $value
     */
    public function isCandidate($value) : bool
    {
        return $value instanceof CurlyListNode;
    }
    /**
     * @param CurlyListNode $value
     */
    public function map($value) : \PhpParser\Node\Expr
    {
        $arrayItems = [];
        $arrayItemNodes = $value->getValues();
        $loop = -1;
        foreach ($arrayItemNodes as $arrayItemNode) {
            $valueExpr = $this->annotationToAttributeMapper->map($arrayItemNode);
            // remove node
            if ($valueExpr === DocTagNodeState::REMOVE_ARRAY) {
                continue;
            }
            Assert::isInstanceOf($valueExpr, ArrayItem::class);
            if (!\is_numeric($arrayItemNode->key)) {
                $arrayItems[] = $valueExpr;
                continue;
            }
            ++$loop;
            $arrayItemNodeKey = (int) $arrayItemNode->key;
            if ($loop === $arrayItemNodeKey) {
                $arrayItems[] = $valueExpr;
                continue;
            }
            $valueExpr->key = new LNumber($arrayItemNodeKey);
            $arrayItems[] = $valueExpr;
        }
        return new Array_($arrayItems);
    }
}
