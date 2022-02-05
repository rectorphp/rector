<?php

declare(strict_types=1);

namespace Rector\PhpAttribute\AnnotationToAttributeMapper;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\PhpAttribute\AnnotationToAttributeMapper;
use Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;
use Rector\PhpAttribute\Enum\DocTagNodeState;
use Symfony\Contracts\Service\Attribute\Required;
use Webmozart\Assert\Assert;

/**
 * @implements AnnotationToAttributeMapperInterface<CurlyListNode>
 */
final class CurlyListNodeAnnotationToAttributeMapper implements AnnotationToAttributeMapperInterface
{
    private AnnotationToAttributeMapper $annotationToAttributeMapper;

    /**
     * Avoid circular reference
     */
    #[Required]
    public function autowire(AnnotationToAttributeMapper $annotationToAttributeMapper): void
    {
        $this->annotationToAttributeMapper = $annotationToAttributeMapper;
    }

    public function isCandidate(mixed $value): bool
    {
        return $value instanceof CurlyListNode;
    }

    /**
     * @param CurlyListNode $value
     */
    public function map($value): Array_
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
            if (! is_int($key)) {
                $keyExpr = $this->annotationToAttributeMapper->map($key);
                Assert::isInstanceOf($keyExpr, Expr::class);
            }

            $arrayItems[] = new ArrayItem($valueExpr, $keyExpr);
        }

        return new Array_($arrayItems);
    }
}
