<?php

declare(strict_types=1);

namespace Rector\DowngradePhp73\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Name;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DowngradePhp73\Tests\Rector\FuncCall\SetCookieOptionsArrayToArgumentsRector\SetCookieOptionsArrayToArgumentsRectorTest
 */
final class SetCookieOptionsArrayToArgumentsRector extends AbstractRector
{
    /**
     * Conversion table from argument index to options name
     * @var array<int, string>
     */
    private const ARGUMENTS_ORDER = [
        'expires' => 2,
        'path' => 3,
        'domain' => 4,
        'secure' => 5,
        'httponly' => 6,
    ];

    /** @var int */
    private $highestIndex = 1;

    /** @var Arg[] */
    private $newArguments = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Convert setcookie option array to arguments',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
setcookie('name', $value, ['expires' => 360]);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
setcookie('name', $value, 360);
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
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $node->args = $this->composeNewArgs($node);

        return $node;
    }

    private function shouldSkip(FuncCall $funcCall): bool
    {
        if (! $this->isNames($funcCall, ['setcookie', 'setrawcookie'])) {
            return true;
        }

        $argsCount = count($funcCall->args);
        if ($argsCount <= 2) {
            return true;
        }

        if (! ($funcCall->args[2]->value instanceof Array_)) {
            return true;
        }

        return false;
    }

    /**
     * @return Arg[]
     */
    private function composeNewArgs(FuncCall $funcCall): array
    {
        $this->highestIndex = 1;
        $this->newArguments = [
            0 => $funcCall->args[0],
            1 => $funcCall->args[1],
        ];

        /** @var Array_ $optionsArray */
        $optionsArray = $funcCall->args[2]->value;
        /** @var ArrayItem $arrayItem */
        foreach ($optionsArray->items as $arrayItem) {
            $value = $arrayItem->value;
            $name = $arrayItem->key->value;

            if (! $this->isMappableArrayKey($name)) {
                continue;
            }

            $order = self::ARGUMENTS_ORDER[$name];
            if ($order > $this->highestIndex) {
                $this->highestIndex = $order;
            }

            $this->newArguments[$order] = $value;
        }

        $this->fillMissingArgumentsWithDefaultValues();
        ksort($this->newArguments);

        return $this->newArguments;
    }

    private function isMappableArrayKey(string $key): bool
    {
        return isset(self::ARGUMENTS_ORDER[$key]);
    }

    private function fillMissingArgumentsWithDefaultValues(): void
    {
        for ($i = 1; $i < $this->highestIndex; $i++) {
            if (isset($this->newArguments[$i])) {
                continue;
            }

            $this->newArguments[$i] = $this->getDefaultValue($i);
        }
    }

    private function getDefaultValue(int $argumentIndex): Arg
    {
        switch ($argumentIndex) {
            case self::ARGUMENTS_ORDER['expires']:
                return new Arg(new LNumber(0));
            case self::ARGUMENTS_ORDER['path']:
            case self::ARGUMENTS_ORDER['domain']:
                return new Arg(new String_(''));
            case self::ARGUMENTS_ORDER['secure']:
            case self::ARGUMENTS_ORDER['httponly']:
                return new Arg(new ConstFetch(new Name('false')));
            default:
                throw new ShouldNotHappenException();
        }
    }
}
