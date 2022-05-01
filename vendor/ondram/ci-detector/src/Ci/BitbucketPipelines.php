<?php

declare (strict_types=1);
namespace RectorPrefix20220501\OndraM\CiDetector\Ci;

use RectorPrefix20220501\OndraM\CiDetector\CiDetector;
use RectorPrefix20220501\OndraM\CiDetector\Env;
use RectorPrefix20220501\OndraM\CiDetector\TrinaryLogic;
class BitbucketPipelines extends \RectorPrefix20220501\OndraM\CiDetector\Ci\AbstractCi
{
    public static function isDetected(\RectorPrefix20220501\OndraM\CiDetector\Env $env) : bool
    {
        return $env->get('BITBUCKET_COMMIT') !== \false;
    }
    public function getCiName() : string
    {
        return \RectorPrefix20220501\OndraM\CiDetector\CiDetector::CI_BITBUCKET_PIPELINES;
    }
    public function isPullRequest() : \RectorPrefix20220501\OndraM\CiDetector\TrinaryLogic
    {
        return \RectorPrefix20220501\OndraM\CiDetector\TrinaryLogic::createFromBoolean($this->env->getString('BITBUCKET_PR_ID') !== '');
    }
    public function getBuildNumber() : string
    {
        return $this->env->getString('BITBUCKET_BUILD_NUMBER');
    }
    public function getBuildUrl() : string
    {
        return \sprintf('%s/addon/pipelines/home#!/results/%s', $this->env->get('BITBUCKET_GIT_HTTP_ORIGIN'), $this->env->get('BITBUCKET_BUILD_NUMBER'));
    }
    public function getCommit() : string
    {
        return $this->env->getString('BITBUCKET_COMMIT');
    }
    public function getBranch() : string
    {
        return $this->env->getString('BITBUCKET_BRANCH');
    }
    public function getTargetBranch() : string
    {
        return $this->env->getString('BITBUCKET_PR_DESTINATION_BRANCH');
    }
    public function getRepositoryName() : string
    {
        return $this->env->getString('BITBUCKET_REPO_FULL_NAME');
    }
    public function getRepositoryUrl() : string
    {
        return \sprintf('ssh://%s', $this->env->get('BITBUCKET_GIT_SSH_ORIGIN'));
    }
}
