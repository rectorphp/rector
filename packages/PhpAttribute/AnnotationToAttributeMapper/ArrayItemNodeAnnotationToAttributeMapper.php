<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\AnnotationToAttributeMapper;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\Core\Validation\RectorAssert;
use Rector\PhpAttribute\AnnotationToAttributeMapper;
use Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;
use Rector\PhpAttribute\Enum\DocTagNodeState;
use RectorPrefix202212\Symfony\Contracts\Service\Attribute\Required;
use RectorPrefix202212\Webmozart\Assert\InvalidArgumentException;
/**
 * @implements AnnotationToAttributeMapperInterface<ArrayItemNode>
 */
final class ArrayItemNodeAnnotationToAttributeMapper implements AnnotationToAttributeMapperInterface
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
        return $value instanceof ArrayItemNode;
    }
    /**
     * @param ArrayItemNode $arrayItemNode
     */
    public function map($arrayItemNode) : Expr
    {
        $valueExpr = $this->annotationToAttributeMapper->map($arrayItemNode->value);
        if ($valueExpr === DocTagNodeState::REMOVE_ARRAY) {
            return new ArrayItem(new String_($valueExpr), null);
        }
        if ($arrayItemNode->key !== null) {
            switch ($arrayItemNode->kindKeyQuoted) {
                case String_::KIND_SINGLE_QUOTED:
                    $keyValue = "'" . $arrayItemNode->key . "'";
                    break;
                case String_::KIND_DOUBLE_QUOTED:
                    $keyValue = '"' . $arrayItemNode->key . '"';
                    break;
                default:
                    $keyValue = $arrayItemNode->key;
                    break;
            }
            /** @var Expr $keyExpr */
            $keyExpr = $this->annotationToAttributeMapper->map($keyValue);
        } else {
            if ($this->hasNoParenthesesAnnotation($arrayItemNode)) {
                try {
                    RectorAssert::className(\ltrim((string) $arrayItemNode->value, '@'));
                    $identifierTypeNode = new IdentifierTypeNode($arrayItemNode->value);
                    $arrayItemNode->value = new DoctrineAnnotationTagValueNode($identifierTypeNode);
                    return $this->map($arrayItemNode);
                } catch (InvalidArgumentException $exception) {
                }
            }
            $keyExpr = null;
        }
        // @todo how to skip natural integer keys?
        return new ArrayItem($valueExpr, $keyExpr);
    }
    private function hasNoParenthesesAnnotation(ArrayItemNode $arrayItemNode) : bool
    {
        if (\is_int($arrayItemNode->kindValueQuoted)) {
            return \false;
        }
        if (!\is_string($arrayItemNode->value)) {
            return \false;
        }
        if (\strncmp($arrayItemNode->value, '@', \strlen('@')) !== 0) {
            return \false;
        }
        return \substr_compare($arrayItemNode->value, ')', -\strlen(')')) !== 0;
    }
}
