<?php

declare (strict_types=1);
namespace Rector\Php85\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\NodeAnalyzer\ReturnAnalyzer;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://3v4l.org/51uu0
 * @see https://3v4l.org/ktJnk
 * @see https://wiki.php.net/rfc/deprecations_php_8_5#deprecate_the_sleep_and_wakeup_magic_methods
 * @see \Rector\Tests\Php85\Rector\Class_\SleepToSerializeRector\SleepToSerializeRectorTest
 */
final class SleepToSerializeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private ReturnAnalyzer $returnAnalyzer;
    public function __construct(BetterNodeFinder $betterNodeFinder, ReturnAnalyzer $returnAnalyzer)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->returnAnalyzer = $returnAnalyzer;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::DEPRECATED_METHOD_SLEEP;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change __sleep() to __serialize() with correct return values', [new CodeSample(<<<'CODE_SAMPLE'
class User {
    private $id;
    private $name;

    public function __sleep() {
        return ['id', 'name'];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class User {
    private $id;
    private $name;

    public function __serialize(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->getMethod('__serialize') instanceof ClassMethod) {
            return null;
        }
        $classMethod = $node->getMethod('__sleep');
        if (!$classMethod instanceof ClassMethod) {
            return null;
        }
        if ($classMethod->returnType instanceof Identifier && $this->isName($classMethod->returnType, 'array')) {
            return null;
        }
        $returns = $this->betterNodeFinder->findReturnsScoped($classMethod);
        if (!$this->returnAnalyzer->hasOnlyReturnWithExpr($classMethod, $returns)) {
            return null;
        }
        $hasChanged = \false;
        foreach ($returns as $return) {
            if (!$return->expr instanceof Array_) {
                return null;
            }
            if ($return->expr->items !== []) {
                $newItems = [];
                foreach ($return->expr->items as $item) {
                    if ($item !== null && $item->value instanceof String_) {
                        $propName = $item->value->value;
                        $newItems[] = new ArrayItem(new PropertyFetch(new Variable('this'), $propName), $item->value);
                    }
                }
                if ($newItems !== []) {
                    $hasChanged = \true;
                    $return->expr->items = $newItems;
                }
            }
        }
        if ($hasChanged) {
            $classMethod->name = new Identifier('__serialize');
            $classMethod->returnType = new Identifier('array');
            return $node;
        }
        return null;
    }
}
