<?php

declare (strict_types=1);
namespace RectorPrefix20210514\Symplify\SetConfigResolver;

use RectorPrefix20210514\Symplify\SetConfigResolver\Config\SetsParameterResolver;
use RectorPrefix20210514\Symplify\SetConfigResolver\Contract\SetProviderInterface;
use Symplify\SmartFileSystem\SmartFileInfo;
/**
 * @see \Symplify\SetConfigResolver\Tests\ConfigResolver\SetAwareConfigResolverTest
 */
final class SetAwareConfigResolver extends \RectorPrefix20210514\Symplify\SetConfigResolver\AbstractConfigResolver
{
    /**
     * @var SetsParameterResolver
     */
    private $setsParameterResolver;
    public function __construct(\RectorPrefix20210514\Symplify\SetConfigResolver\Contract\SetProviderInterface $setProvider)
    {
        $setResolver = new \RectorPrefix20210514\Symplify\SetConfigResolver\SetResolver($setProvider);
        $this->setsParameterResolver = new \RectorPrefix20210514\Symplify\SetConfigResolver\Config\SetsParameterResolver($setResolver);
        parent::__construct();
    }
    /**
     * @param SmartFileInfo[] $fileInfos
     * @return SmartFileInfo[]
     */
    public function resolveFromParameterSetsFromConfigFiles(array $fileInfos) : array
    {
        return $this->setsParameterResolver->resolveFromFileInfos($fileInfos);
    }
}
