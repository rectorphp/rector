<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Helper;

use RectorPrefix20220606\PhpParser\Node\Expr\ClassConstFetch;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\NodeFactory;
final class OldSeverityToLogLevelMapper
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }
    public function mapSeverityToLogLevel(int $severityValue) : ClassConstFetch
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
