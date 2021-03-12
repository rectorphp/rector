<?php
declare(strict_types=1);


namespace Rector\PHPUnit\ValueObject;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;

final class ExpectationMock
{

    /**
     * @var Expr
     */
    private $var;

    /**
     * @var Arg[]
     */
    private $methodArguments;

    /**
     * @var int
     */
    private $index;

    /**
     * @var ?Expr
     */
    private $return;

    /**
     * @var array<int, null|Expr>
     */
    private $withArguments;

    /**
     * @param Arg[] $methodArguments
     * @param array<int, null|Expr> $withArguments
     */
    public function __construct(Expr $var, array $methodArguments, int $index, ?Expr $return, array $withArguments)
    {
        $this->var = $var;
        $this->methodArguments = $methodArguments;
        $this->index = $index;
        $this->return = $return;
        $this->withArguments = $withArguments;
    }

    public function getVar(): Expr
    {
        return $this->var;
    }

    /**
     * @return Arg[]
     */
    public function getMethodArguments(): array
    {
        return $this->methodArguments;
    }

    public function getIndex(): int
    {
        return $this->index;
    }

    public function getReturn(): ?Expr
    {
        return $this->return;
    }

    /**
     * @return array<int, null|Expr>
     */
    public function getWithArguments(): array
    {
        return $this->withArguments;
    }
}
