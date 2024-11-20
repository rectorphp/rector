<?php

declare (strict_types=1);
namespace Rector\PhpParser\Node\CustomNode;

use PhpParser\Node\Stmt;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
/**
 * Inspired by https://github.com/phpstan/phpstan-src/commit/ed81c3ad0b9877e6122c79b4afda9d10f3994092
 */
final class FileWithoutNamespace extends Stmt implements StmtsAwareInterface
{
    /**
     * @var Stmt[]
     */
    public array $stmts;
    /**
     * @param Stmt[] $stmts
     */
    public function __construct(array $stmts)
    {
        $this->stmts = $stmts;
        parent::__construct();
    }
    public function getType() : string
    {
        return 'FileWithoutNamespace';
    }
    /**
     * @return string[]
     */
    public function getSubNodeNames() : array
    {
        return ['stmts'];
    }
}
