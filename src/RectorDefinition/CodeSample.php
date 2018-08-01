<?php declare(strict_types=1);

namespace Rector\RectorDefinition;

use Rector\Contract\RectorDefinition\CodeSampleInterface;

final class CodeSample implements CodeSampleInterface
{
    /**
     * @var string
     */
    private $codeBefore;

    /**
     * @var string
     */
    private $codeAfter;

    public function __construct(string $codeBefore, string $codeAfter)
    {
        $this->codeBefore = $codeBefore;
        $this->codeAfter = $codeAfter;
    }

    public function getCodeBefore(): string
    {
        return $this->codeBefore;
    }

    public function getCodeAfter(): string
    {
        return $this->codeAfter;
    }
}
