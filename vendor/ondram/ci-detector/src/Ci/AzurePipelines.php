<?php

declare (strict_types=1);
namespace RectorPrefix20220531\OndraM\CiDetector\Ci;

use RectorPrefix20220531\OndraM\CiDetector\CiDetector;
use RectorPrefix20220531\OndraM\CiDetector\Env;
use RectorPrefix20220531\OndraM\CiDetector\TrinaryLogic;
class AzurePipelines extends \RectorPrefix20220531\OndraM\CiDetector\Ci\AbstractCi
{
    public static function isDetected(\RectorPrefix20220531\OndraM\CiDetector\Env $env) : bool
    {
        return $env->get('BUILD_DEFINITIONVERSION') !== \false;
    }
    public function getCiName() : string
    {
        return \RectorPrefix20220531\OndraM\CiDetector\CiDetector::CI_AZURE_PIPELINES;
    }
    public function isPullRequest() : \RectorPrefix20220531\OndraM\CiDetector\TrinaryLogic
    {
        return \RectorPrefix20220531\OndraM\CiDetector\TrinaryLogic::createFromBoolean($this->env->getString('BUILD_REASON') === 'PullRequest');
    }
    public function getBuildNumber() : string
    {
        return $this->env->getString('BUILD_BUILDNUMBER');
    }
    public function getBuildUrl() : string
    {
        return \sprintf('%s%s/_build/results?buildId=%s', $this->env->get('SYSTEM_COLLECTIONURI'), $this->env->get('SYSTEM_TEAMPROJECT'), $this->env->get('BUILD_BUILDID'));
    }
    public function getCommit() : string
    {
        return $this->env->getString('BUILD_SOURCEVERSION');
    }
    public function getBranch() : string
    {
        if ($this->isPullRequest()->no()) {
            return $this->env->getString('BUILD_SOURCEBRANCHNAME');
        }
        return $this->env->getString('SYSTEM_PULLREQUEST_SOURCEBRANCH');
    }
    public function getTargetBranch() : string
    {
        return $this->env->getString('SYSTEM_PULLREQUEST_TARGETBRANCH');
    }
    public function getRepositoryName() : string
    {
        return $this->env->getString('BUILD_REPOSITORY_NAME');
    }
    public function getRepositoryUrl() : string
    {
        return $this->env->getString('BUILD_REPOSITORY_URI');
    }
}
