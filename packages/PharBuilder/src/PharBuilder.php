<?php declare(strict_types=1);

namespace Rector\PharBuilder;

use Rector\PharBuilder\Compiler\Compiler;

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
        $this->init();
        $this->compiler->compile($buildDirectory);
    }

    private function init(): void
    {
        ini_set('phar.readonly', '0');
    }
}
