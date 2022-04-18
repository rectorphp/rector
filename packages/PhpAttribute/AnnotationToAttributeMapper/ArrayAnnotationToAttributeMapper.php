<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\AnnotationToAttributeMapper;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use Rector\PhpAttribute\AnnotationToAttributeMapper;
use Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;
use Rector\PhpAttribute\Enum\DocTagNodeState;
use RectorPrefix20220418\Symfony\Contracts\Service\Attribute\Required;
use RectorPrefix20220418\Webmozart\Assert\Assert;
/**
 * @implements AnnotationToAttributeMapperInterface<mixed[]>
 */
final class ArrayAnnotationToAttributeMapper implements \Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface
{
    /**
     * @var \Rector\PhpAttribute\AnnotationToAttributeMapper
     */
    private $annotationToAttributeMapper;
    /**
     * Avoid circular reference
     * @required
     */
    public function autowire(\Rector\PhpAttribute\AnnotationToAttributeMapper $annotationToAttributeMapper) : void
    {
        $this->annotationToAttributeMapper = $annotationToAttributeMapper;
    }
    /**
     * @param mixed $value
     */
    public function isCandidate($value) : bool
    {
        return \is_array($value);
    }
    /**
     * @param mixed[] $value
     */
    public function map($value) : \PhpParser\Node\Expr
    {
        $arrayItems = [];
        foreach ($value as $key => $singleValue) {
            $valueExpr = $this->annotationToAttributeMapper->map($singleValue);
            // remove node
            if ($valueExpr === \Rector\PhpAttribute\Enum\DocTagNodeState::REMOVE_ARRAY) {
                continue;
            }
            \RectorPrefix20220418\Webmozart\Assert\Assert::isInstanceOf($valueExpr, \PhpParser\Node\Expr::class);
            // remove value
            if ($this->isRemoveArrayPlaceholder($singleValue)) {
                continue;
            }
            $keyExpr = null;
            if (!\is_int($key)) {
                $keyExpr = $this->annotationToAttributeMapper->map($key);
                \RectorPrefix20220418\Webmozart\Assert\Assert::isInstanceOf($keyExpr, \PhpParser\Node\Expr::class);
            }
            $arrayItems[] = new \PhpParser\Node\Expr\ArrayItem($valueExpr, $keyExpr);
        }
        return new \PhpParser\Node\Expr\Array_($arrayItems);
    }
    /**
     * @param mixed $value
     */
    private function isRemoveArrayPlaceholder($value) : bool
    {
        if (!\is_array($value)) {
            return \false;
        }
        return \in_array(\Rector\PhpAttribute\Enum\DocTagNodeState::REMOVE_ARRAY, $value, \true);
    }
}
