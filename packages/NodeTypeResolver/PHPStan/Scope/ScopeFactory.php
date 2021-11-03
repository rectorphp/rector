<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan\Scope;

use PHPStan\Analyser\MutatingScope;
use PHPStan\Analyser\ScopeContext;
use PHPStan\Analyser\ScopeFactory as PHPStanScopeFactory;
use Symplify\SmartFileSystem\SmartFileInfo;
final class ScopeFactory
{
    /**
     * @var PHPStanScopeFactory
     */
    private $phpStanScopeFactory;
    public function __construct(\PHPStan\Analyser\ScopeFactory $phpStanScopeFactory)
    {
        $this->phpStanScopeFactory = $phpStanScopeFactory;
    }
    public function createFromFile(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo) : \PHPStan\Analyser\MutatingScope
    {
        $scopeContext = \PHPStan\Analyser\ScopeContext::create($fileInfo->getRealPath());
        return $this->phpStanScopeFactory->create($scopeContext);
    }
}
