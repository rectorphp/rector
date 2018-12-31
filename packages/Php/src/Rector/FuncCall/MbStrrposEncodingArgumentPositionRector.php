<?php declare(strict_types=1);

namespace Rector\Php\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\LNumber;
use PHPStan\Type\IntegerType;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://wiki.php.net/rfc/deprecations_php_7_4 (not confirmed yet)
 * @see https://3v4l.org/kLdtB
 */
final class MbStrrposEncodingArgumentPositionRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change mb_strrpos() encoding argument position', [
            new CodeSample('mb_strrpos($text, "abc", "UTF-8");', 'mb_strrpos($text, "abc", 0, "UTF-8");'),
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
        if (! $this->isName($node, 'mb_strrpos')) {
            return null;
        }

        if (! isset($node->args[2])) {
            return null;
        }

        if (isset($node->args[3])) {
            return null;
        }

        if ($this->getStaticType($node->args[2]->value) instanceof IntegerType) {
            return null;
        }

        $node->args[3] = $node->args[2];
        $node->args[2] = new Arg(new LNumber(0));

        return $node;
    }
}
