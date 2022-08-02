<?php

declare (strict_types=1);
namespace RectorPrefix202208\OndraM\CiDetector\Ci;

use RectorPrefix202208\OndraM\CiDetector\Env;
/**
 * Unified adapter to retrieve environment variables from current continuous integration server
 */
abstract class AbstractCi implements CiInterface
{
    /** @var Env */
    protected $env;
    public function __construct(Env $env)
    {
        $this->env = $env;
    }
    public function describe() : array
    {
        return ['ci-name' => $this->getCiName(), 'build-number' => $this->getBuildNumber(), 'build-url' => $this->getBuildUrl(), 'commit' => $this->getCommit(), 'branch' => $this->getBranch(), 'target-branch' => $this->getTargetBranch(), 'repository-name' => $this->getRepositoryName(), 'repository-url' => $this->getRepositoryUrl(), 'is-pull-request' => $this->isPullRequest()->describe()];
    }
}
