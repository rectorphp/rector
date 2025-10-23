<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Concat;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\MagicConst\Dir;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Concat\DirnameDirConcatStringToDirectStringPathRector\DirnameDirConcatStringToDirectStringPathRectorTest
 */
final class DirnameDirConcatStringToDirectStringPathRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change dirname() and string concat, to __DIR__ and direct string path', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $path = dirname(__DIR__) . '/vendor/autoload.php';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $path = __DIR__ . '/../vendor/autoload.php';
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [Concat::class];
    }
    /**
     * @param Concat $node
     */
    public function refactor(Node $node): ?Concat
    {
        if (!$node->left instanceof FuncCall || !$this->isName($node->left, 'dirname')) {
            return null;
        }
        if (!$node->right instanceof String_) {
            return null;
        }
        $dirnameFuncCall = $node->left;
        if ($dirnameFuncCall->isFirstClassCallable()) {
            return null;
        }
        // avoid multiple dir nesting for now
        if (count($dirnameFuncCall->getArgs()) !== 1) {
            return null;
        }
        $firstArg = $dirnameFuncCall->getArgs()[0];
        if (!$firstArg->value instanceof Dir) {
            return null;
        }
        $string = $node->right;
        if (strpos($string->value, '/') !== \false) {
            // linux paths
            $string->value = '/../' . ltrim($string->value, '/');
            $node->left = new Dir();
            return $node;
        }
        if (strpos($string->value, '\\') !== \false) {
            // windows paths
            $string->value = '\..\\' . ltrim($string->value, '\\');
            $node->left = new Dir();
            return $node;
        }
        return null;
    }
}
