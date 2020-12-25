<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Cast;

use PhpParser\Node;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\Cast\Array_;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\Cast\Double;
use PhpParser\Node\Expr\Cast\Int_;
use PhpParser\Node\Expr\Cast\Object_;
use PhpParser\Node\Expr\Cast\String_;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\Cast\RecastingRemovalRector\RecastingRemovalRectorTest
 */
final class RecastingRemovalRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const CAST_CLASS_TO_NODE_TYPE = [
        String_::class => StringType::class,
        Bool_::class => BooleanType::class,
        Array_::class => ArrayType::class,
        Int_::class => IntegerType::class,
        Object_::class => ObjectType::class,
        Double::class => FloatType::class,
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Removes recasting of the same type', [
            new CodeSample(
                <<<'CODE_SAMPLE'
$string = '';
$string = (string) $string;

$array = [];
$array = (array) $array;
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$string = '';
$string = $string;

$array = [];
$array = $array;
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Cast::class];
    }

    /**
     * @param Cast $node
     */
    public function refactor(Node $node): ?Node
    {
        $nodeClass = get_class($node);
        if (! isset(self::CAST_CLASS_TO_NODE_TYPE[$nodeClass])) {
            return null;
        }

        $nodeType = $this->getStaticType($node->expr);
        if ($nodeType instanceof MixedType) {
            return null;
        }

        $sameNodeType = self::CAST_CLASS_TO_NODE_TYPE[$nodeClass];
        if (! is_a($nodeType, $sameNodeType, true)) {
            return null;
        }

        return $node->expr;
    }
}
