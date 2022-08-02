<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\AnnotationToAttributeMapper;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\PhpAttribute\AnnotationToAttributeMapper;
use Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;
use Rector\PhpAttribute\Enum\DocTagNodeState;
use RectorPrefix202208\Symfony\Contracts\Service\Attribute\Required;
use RectorPrefix202208\Webmozart\Assert\Assert;
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
