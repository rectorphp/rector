<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\SetConfigResolver;

use RectorPrefix20210510\Symplify\SetConfigResolver\Config\SetsParameterResolver;
use RectorPrefix20210510\Symplify\SetConfigResolver\Contract\SetProviderInterface;
use Symplify\SmartFileSystem\SmartFileInfo;
/**
 * @see \Symplify\SetConfigResolver\Tests\ConfigResolver\SetAwareConfigResolverTest
 */
final class SetAwareConfigResolver extends AbstractConfigResolver
{
    /**
     * @var SetsParameterResolver
     */
    private $setsParameterResolver;
    public function __construct(SetProviderInterface $setProvider)
    {
        $setResolver = new SetResolver($setProvider);
        $this->setsParameterResolver = new SetsParameterResolver($setResolver);
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
