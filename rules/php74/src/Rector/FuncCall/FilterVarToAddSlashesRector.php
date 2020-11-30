<?php

declare(strict_types=1);

namespace Rector\Php74\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://wiki.php.net/rfc/deprecations_php_7_4 (not confirmed yet)
 * @see https://3v4l.org/9rLjE
 * @see \Rector\Php74\Tests\Rector\FuncCall\FilterVarToAddSlashesRector\FilterVarToAddSlashesRectorTest
 */
final class FilterVarToAddSlashesRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change filter_var() with slash escaping to addslashes()',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
$var= "Satya's here!";
filter_var($var, FILTER_SANITIZE_MAGIC_QUOTES);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$var= "Satya's here!";
addslashes($var);
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
        if (! $this->isName($node, 'filter_var')) {
            return null;
        }

        if (! isset($node->args[1])) {
            return null;
        }

        if (! $this->isName($node->args[1]->value, 'FILTER_SANITIZE_MAGIC_QUOTES')) {
            return null;
        }

        $node->name = new Name('addslashes');
        unset($node->args[1]);

        return $node;
    }
}
