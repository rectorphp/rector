<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Helper;

use PhpParser\Node\Expr\ClassConstFetch;
use Rector\Core\PhpParser\Node\NodeFactory;
final class OldSeverityToLogLevelMapper
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
    public function mapSeverityToLogLevel(int $severityValue) : \PhpParser\Node\Expr\ClassConstFetch
    {
        if (0 === $severityValue) {
            return $this->nodeFactory->createClassConstFetch('TYPO3\\CMS\\Core\\Log\\LogLevel', 'INFO');
        }
        if (1 === $severityValue) {
            return $this->nodeFactory->createClassConstFetch('TYPO3\\CMS\\Core\\Log\\LogLevel', 'NOTICE');
        }
        if (2 === $severityValue) {
            return $this->nodeFactory->createClassConstFetch('TYPO3\\CMS\\Core\\Log\\LogLevel', 'WARNING');
        }
        if (3 === $severityValue) {
            return $this->nodeFactory->createClassConstFetch('TYPO3\\CMS\\Core\\Log\\LogLevel', 'ERROR');
        }
        if (4 === $severityValue) {
            return $this->nodeFactory->createClassConstFetch('TYPO3\\CMS\\Core\\Log\\LogLevel', 'CRITICAL');
        }
        return $this->nodeFactory->createClassConstFetch('TYPO3\\CMS\\Core\\Log\\LogLevel', 'INFO');
    }
}
