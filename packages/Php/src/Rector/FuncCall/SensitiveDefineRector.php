<?php declare(strict_types=1);

namespace Rector\Php\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://wiki.php.net/rfc/case_insensitive_constant_deprecation
 */
final class SensitiveDefineRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes case insensitive constants to sensitive ones.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
define('FOO', 42, true); 
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
define('FOO', 42); 
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $funcCallNode
     */
    public function refactor(Node $funcCallNode): ?Node
    {
        if ((string) $funcCallNode->name !== 'define') {
            return $funcCallNode;
        }

        if (! isset($funcCallNode->args[2])) {
            return $funcCallNode;
        }

        unset($funcCallNode->args[2]);

        return $funcCallNode;
    }
}
