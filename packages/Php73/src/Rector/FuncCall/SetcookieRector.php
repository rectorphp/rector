<?php

declare(strict_types=1);

namespace Rector\Php73\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\ValueObject\PhpVersionFeature;

/**
 * Convert legacy setcookie arguments to new array options
 *
 * @see https://www.php.net/setcookie
 * @see https://wiki.php.net/rfc/same-site-cookie
 */
final class SetcookieRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Convert setcookie argument to PHP7.3 option array',
            [
                new CodeSample(
                    <<<'PHP'
setcookie('name', $value, 360);
PHP
                    ,
                    <<<'PHP'
setcookie('name', $value, ['expires' => 360]);
PHP
                ),
                new CodeSample(
<<<'PHP'
setcookie('name', $name, 0, '', '', true, true);
PHP
                    ,
<<<'PHP'
setcookie('name', $name, ['expires' => 0, 'path' => '', 'domain' => '', 'secure' => true, 'httponly' => true]);
PHP
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
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        return $node;
    }

    private function shouldSkip(FuncCall $funcCall): bool
    {
        if (! $this->isNames($funcCall, ['setcookie'])) {
            return true;
        }

        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::SETCOOKIE_ACCEPT_ARRAY_OPTIONS)) {
            return true;
        }

        $args_count = \count($funcCall->args);

        if ($args_count <= 2) {
            return true;
        }

        if ($funcCall->args[2]->value instanceof Array_) {
            return true;
        }

        if ($args_count === 3) {
            return $funcCall->args[2]->value instanceof Variable;
        }

        return false;
    }
}
