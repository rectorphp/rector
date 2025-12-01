<?php

declare (strict_types=1);
namespace Rector\Renaming\ValueObject;

use PhpParser\Node\Expr\Cast;
use Rector\Validation\RectorAssert;
use RectorPrefix202512\Webmozart\Assert\Assert;
final class RenameCast
{
    /**
     * @var class-string<Cast>
     * @readonly
     */
    private string $fromCastExprClass;
    /**
     * @readonly
     */
    private int $fromCastKind;
    /**
     * @readonly
     */
    private int $toCastKind;
    /**
     * @param class-string<Cast> $fromCastExprClass
     */
    public function __construct(string $fromCastExprClass, int $fromCastKind, int $toCastKind)
    {
        $this->fromCastExprClass = $fromCastExprClass;
        $this->fromCastKind = $fromCastKind;
        $this->toCastKind = $toCastKind;
        RectorAssert::className($fromCastExprClass);
        Assert::subclassOf($fromCastExprClass, Cast::class);
    }
    /**
     * @return class-string<Cast>
     */
    public function getFromCastExprClass(): string
    {
        return $this->fromCastExprClass;
    }
    public function getFromCastKind(): int
    {
        return $this->fromCastKind;
    }
    public function getToCastKind(): int
    {
        return $this->toCastKind;
    }
}
