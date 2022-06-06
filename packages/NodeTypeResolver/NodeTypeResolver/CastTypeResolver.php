<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\NodeTypeResolver\NodeTypeResolver;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\Cast;
use RectorPrefix20220606\PhpParser\Node\Expr\Cast\Array_;
use RectorPrefix20220606\PhpParser\Node\Expr\Cast\Bool_;
use RectorPrefix20220606\PhpParser\Node\Expr\Cast\Double;
use RectorPrefix20220606\PhpParser\Node\Expr\Cast\Int_;
use RectorPrefix20220606\PhpParser\Node\Expr\Cast\Object_;
use RectorPrefix20220606\PhpParser\Node\Expr\Cast\String_;
use RectorPrefix20220606\PHPStan\Type\ArrayType;
use RectorPrefix20220606\PHPStan\Type\BooleanType;
use RectorPrefix20220606\PHPStan\Type\FloatType;
use RectorPrefix20220606\PHPStan\Type\IntegerType;
use RectorPrefix20220606\PHPStan\Type\MixedType;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\PHPStan\Type\StringType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\Rector\Core\Exception\NotImplementedYetException;
use RectorPrefix20220606\Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
/**
 * @implements NodeTypeResolverInterface<Cast>
 */
final class CastTypeResolver implements NodeTypeResolverInterface
{
    /**
     * @var array<class-string<Node>, class-string<Type>>
     */
    private const CAST_CLASS_TO_TYPE_MAP = [Bool_::class => BooleanType::class, String_::class => StringType::class, Int_::class => IntegerType::class, Double::class => FloatType::class];
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses() : array
    {
        return [Cast::class];
    }
    /**
     * @param Cast $node
     */
    public function resolve(Node $node) : Type
    {
        foreach (self::CAST_CLASS_TO_TYPE_MAP as $castClass => $typeClass) {
            if (\is_a($node, $castClass, \true)) {
                return new $typeClass();
            }
        }
        if ($node instanceof Array_) {
            return new ArrayType(new MixedType(), new MixedType());
        }
        if ($node instanceof Object_) {
            return new ObjectType('stdClass');
        }
        throw new NotImplementedYetException(\get_class($node));
    }
}
