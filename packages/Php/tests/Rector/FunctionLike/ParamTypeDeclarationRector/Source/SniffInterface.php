<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Source;

interface SniffInterface
{
    /**
     * @param int $position
     */
    public function process(string $file, $position);
}
