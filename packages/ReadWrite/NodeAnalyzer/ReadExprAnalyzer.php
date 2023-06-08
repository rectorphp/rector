<?php

declare (strict_types=1);
namespace Rector\ReadWrite\NodeAnalyzer;

use PhpParser\Node\Expr;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\ReadWrite\Contract\ReadNodeAnalyzerInterface;
use Rector\ReadWrite\ReadNodeAnalyzer\LocalPropertyFetchReadNodeAnalyzer;
use Rector\ReadWrite\ReadNodeAnalyzer\VariableReadNodeAnalyzer;
final class ReadExprAnalyzer
{
    /**
     * @var ReadNodeAnalyzerInterface[]
     */
    private $readNodeAnalyzers = [];
    public function __construct(VariableReadNodeAnalyzer $variableReadNodeAnalyzer, LocalPropertyFetchReadNodeAnalyzer $localPropertyFetchReadNodeAnalyzer)
    {
        $this->readNodeAnalyzers = [$variableReadNodeAnalyzer, $localPropertyFetchReadNodeAnalyzer];
    }
    /**
     * Is the value read or used for read purpose (at least, not only)
     */
    public function isExprRead(Expr $expr) : bool
    {
        foreach ($this->readNodeAnalyzers as $readNodeAnalyzer) {
            if (!$readNodeAnalyzer->supports($expr)) {
                continue;
            }
            return $readNodeAnalyzer->isRead($expr);
        }
        throw new NotImplementedYetException(\get_class($expr));
    }
}
