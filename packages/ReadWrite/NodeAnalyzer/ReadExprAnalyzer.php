<?php

declare (strict_types=1);
namespace Rector\ReadWrite\NodeAnalyzer;

use PhpParser\Node\Expr;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\ReadWrite\Contract\ReadNodeAnalyzerInterface;
final class ReadExprAnalyzer
{
    /**
     * @var \Rector\ReadWrite\Contract\ReadNodeAnalyzerInterface[]
     */
    private $readNodeAnalyzers;
    /**
     * @param ReadNodeAnalyzerInterface[] $readNodeAnalyzers
     */
    public function __construct(array $readNodeAnalyzers)
    {
        $this->readNodeAnalyzers = $readNodeAnalyzers;
    }
    /**
     * Is the value read or used for read purpose (at least, not only)
     */
    public function isExprRead(\PhpParser\Node\Expr $expr) : bool
    {
        foreach ($this->readNodeAnalyzers as $readNodeAnalyzer) {
            if (!$readNodeAnalyzer->supports($expr)) {
                continue;
            }
            return $readNodeAnalyzer->isRead($expr);
        }
        throw new \Rector\Core\Exception\NotImplementedYetException(\get_class($expr));
    }
}
