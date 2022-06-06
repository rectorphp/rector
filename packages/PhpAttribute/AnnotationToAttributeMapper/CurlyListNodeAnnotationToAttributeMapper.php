<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\PhpAttribute\AnnotationToAttributeMapper;

use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\Array_;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayItem;
use RectorPrefix20220606\Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use RectorPrefix20220606\Rector\PhpAttribute\AnnotationToAttributeMapper;
use RectorPrefix20220606\Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;
use RectorPrefix20220606\Rector\PhpAttribute\Enum\DocTagNodeState;
use RectorPrefix20220606\Symfony\Contracts\Service\Attribute\Required;
use RectorPrefix20220606\Webmozart\Assert\Assert;
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
    public function map($value) : \RectorPrefix20220606\PhpParser\Node\Expr
    {
        $arrayItems = [];
        foreach ($value->getValuesWithExplicitSilentAndWithoutQuotes() as $key => $singleValue) {
            $valueExpr = $this->annotationToAttributeMapper->map($singleValue);
            // remove node
            if ($valueExpr === DocTagNodeState::REMOVE_ARRAY) {
                continue;
            }
            Assert::isInstanceOf($valueExpr, Expr::class);
            $keyExpr = null;
            if (!\is_int($key)) {
                $keyExpr = $this->annotationToAttributeMapper->map($key);
                Assert::isInstanceOf($keyExpr, Expr::class);
            }
            $arrayItems[] = new ArrayItem($valueExpr, $keyExpr);
        }
        return new Array_($arrayItems);
    }
}
