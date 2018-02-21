<?php declare(strict_types=1);

namespace Rector\PharBuilder;

final class PharBuilder
{
    /**
     * @var Compiler
     */
    private $compiler;

    public function __construct(Compiler $compiler)
    {
        $this->compiler = $compiler;
    }

    public function build(string $buildDirectory): void
    {
        $this->compiler->compile($buildDirectory);
    }
}
