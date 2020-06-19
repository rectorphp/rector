<?php

declare(strict_types=1);

namespace Rector\Core\ValueObject\Application;

use PhpParser\Node;

final class ParsedStmtsAndTokens
{
    /**
     * @var Node[]
     */
    private $newStmts = [];

    /**
     * @var Node[]
     */
    private $oldStmts = [];

    /**
     * @var mixed[]
     */
    private $oldTokens = [];

    /**
     * @param Node[] $newStmts
     * @param Node[] $oldStmts
     * @param mixed[] $oldTokens
     */
    public function __construct(array $newStmts, array $oldStmts, array $oldTokens)
    {
        $this->newStmts = $newStmts;
        $this->oldStmts = $oldStmts;
        $this->oldTokens = $oldTokens;
    }

    /**
     * @return Node[]
     */
    public function getNewStmts(): array
    {
        return $this->newStmts;
    }

    /**
     * @return Node[]
     */
    public function getOldStmts(): array
    {
        return $this->oldStmts;
    }

    /**
     * @return mixed[]
     */
    public function getOldTokens(): array
    {
        return $this->oldTokens;
    }

    /**
     * @param Node[] $newStmts
     */
    public function updateNewStmts(array $newStmts): void
    {
        $this->newStmts = $newStmts;
    }
}
