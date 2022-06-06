<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Helper\Database\Refactorings;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use Rector\Core\PhpParser\Node\NodeFactory;
final class ConnectionCallFactory
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(\Rector\Core\PhpParser\Node\NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }
    public function createConnectionCall(\PhpParser\Node\Arg $firstArgument) : \PhpParser\Node\Expr\Assign
    {
        $connection = $this->nodeFactory->createMethodCall($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\Database\\ConnectionPool')]), 'getConnectionForTable', [$this->nodeFactory->createArg($firstArgument->value)]);
        return new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable('connection'), $connection);
    }
}
