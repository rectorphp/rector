<?php declare(strict_types=1);

namespace Rector\Php\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see http://php.net/manual/en/migration72.incompatible.php#migration72.incompatible.is_object-on-incomplete_class
 * @see https://3v4l.org/SpiE6
 */
final class IsObjectOnIncompleteClassRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Incomplete class returns inverted bool on is_object()', [
            new CodeSample(
                <<<'CODE_SAMPLE'
$incompleteObject = new __PHP_Incomplete_Class;
$isObject = is_object($incompleteObject);
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$incompleteObject = new __PHP_Incomplete_Class;
$isObject = ! is_object($incompleteObject);
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node, 'is_object')) {
            return null;
        }

        $types = $this->getTypes($node->args[0]->value);
        if ($types !== ['__PHP_Incomplete_Class']) {
            return null;
        }

        if ($this->shouldSkip($node)) {
            return null;
        }

        $booleanNotNode = new BooleanNot($node);
        $node->setAttribute(Attribute::PARENT_NODE, $booleanNotNode);

        return $booleanNotNode;
    }

    private function shouldSkip(FuncCall $funcCallNode): bool
    {
        $parentNode = $funcCallNode->getAttribute(Attribute::PARENT_NODE);
        return $parentNode instanceof BooleanNot;
    }
}
