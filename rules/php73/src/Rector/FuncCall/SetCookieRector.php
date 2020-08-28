<?php

declare(strict_types=1);

namespace Rector\Php73\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\PhpVersionFeature;

/**
 * Convert legacy setcookie arguments to new array options
 *
 * @see \Rector\Php73\Tests\Rector\FuncCall\SetcookieRector\SetCookieRectorTest
 *
 * @see https://www.php.net/setcookie
 * @see https://wiki.php.net/rfc/same-site-cookie
 */
final class SetCookieRector extends AbstractRector
{
    /**
     * Conversion table from argument index to options name
     * @var string[]
     */
    private const KNOWN_OPTIONS = [
        2 => 'expires',
        3 => 'path',
        4 => 'domain',
        5 => 'secure',
        6 => 'httponly',
    ];

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

        /** @var FuncCall $node */
        $node->args = $this->composeNewArgs($node);

        return $node;
    }

    private function shouldSkip(FuncCall $funcCall): bool
    {
        if (! $this->isNames($funcCall, ['setcookie', 'setrawcookie'])) {
            return true;
        }

        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::SETCOOKIE_ACCEPT_ARRAY_OPTIONS)) {
            return true;
        }

        $argsCount = count($funcCall->args);

        if ($argsCount <= 2) {
            return true;
        }

        if ($funcCall->args[2]->value instanceof Array_) {
            return true;
        }

        if ($argsCount === 3) {
            return $funcCall->args[2]->value instanceof Variable;
        }

        return false;
    }

    /**
     * @return Arg[]
     */
    private function composeNewArgs(FuncCall $funcCall): array
    {
        $args = $funcCall->args;
        $arguments[] = array_pop($args);
        $arguments[] = array_pop($args);

        foreach ($args as $idx => $arg) {
            $newKey = new String_(self::KNOWN_OPTIONS[$idx]);
            $arguments[] = new ArrayItem($arg->value, $newKey);
        }

        return $this->createArgs($arguments);
    }
}
