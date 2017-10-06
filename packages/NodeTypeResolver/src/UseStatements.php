<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

final class UseStatements
{
    /**
     * @var string[]
     */
    private $useStatements = [];

    public function addUseStatement(string $useStatement): void
    {
        $this->useStatements[] = $useStatement;
    }

    /**
     * @return string[]
     */
    public function getUseStatements(): array
    {
        return $this->useStatements;
    }
}
