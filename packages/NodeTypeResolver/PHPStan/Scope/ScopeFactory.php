<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\NodeTypeResolver\PHPStan\Scope;

use RectorPrefix20220606\PHPStan\Analyser\MutatingScope;
use RectorPrefix20220606\PHPStan\Analyser\ScopeContext;
use RectorPrefix20220606\PHPStan\Analyser\ScopeFactory as PHPStanScopeFactory;
use Symplify\SmartFileSystem\SmartFileInfo;
final class ScopeFactory
{
    /**
     * @readonly
     * @var PHPStanScopeFactory
     */
    private $phpStanScopeFactory;
    public function __construct(PHPStanScopeFactory $phpStanScopeFactory)
    {
        $this->phpStanScopeFactory = $phpStanScopeFactory;
    }
    public function createFromFile(SmartFileInfo $fileInfo) : MutatingScope
    {
        $scopeContext = ScopeContext::create($fileInfo->getRealPath());
        return $this->phpStanScopeFactory->create($scopeContext);
    }
}
