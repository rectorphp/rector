<?php

declare (strict_types=1);
namespace RectorPrefix20220531\OndraM\CiDetector\Ci;

use RectorPrefix20220531\OndraM\CiDetector\CiDetector;
use RectorPrefix20220531\OndraM\CiDetector\Env;
use RectorPrefix20220531\OndraM\CiDetector\TrinaryLogic;
class GitLab extends \RectorPrefix20220531\OndraM\CiDetector\Ci\AbstractCi
{
    public static function isDetected(\RectorPrefix20220531\OndraM\CiDetector\Env $env) : bool
    {
        return $env->get('GITLAB_CI') !== \false;
    }
    public function getCiName() : string
    {
        return \RectorPrefix20220531\OndraM\CiDetector\CiDetector::CI_GITLAB;
    }
    public function isPullRequest() : \RectorPrefix20220531\OndraM\CiDetector\TrinaryLogic
    {
        return \RectorPrefix20220531\OndraM\CiDetector\TrinaryLogic::createFromBoolean($this->env->get('CI_MERGE_REQUEST_ID') !== \false || $this->env->get('CI_EXTERNAL_PULL_REQUEST_IID') !== \false);
    }
    public function getBuildNumber() : string
    {
        return !empty($this->env->getString('CI_JOB_ID')) ? $this->env->getString('CI_JOB_ID') : $this->env->getString('CI_BUILD_ID');
    }
    public function getBuildUrl() : string
    {
        return $this->env->getString('CI_PROJECT_URL') . '/builds/' . $this->getBuildNumber();
    }
    public function getCommit() : string
    {
        return !empty($this->env->getString('CI_COMMIT_SHA')) ? $this->env->getString('CI_COMMIT_SHA') : $this->env->getString('CI_BUILD_REF');
    }
    public function getBranch() : string
    {
        return !empty($this->env->getString('CI_COMMIT_REF_NAME')) ? $this->env->getString('CI_COMMIT_REF_NAME') : $this->env->getString('CI_BUILD_REF_NAME');
    }
    public function getTargetBranch() : string
    {
        return !empty($this->env->getString('CI_EXTERNAL_PULL_REQUEST_TARGET_BRANCH_NAME')) ? $this->env->getString('CI_EXTERNAL_PULL_REQUEST_TARGET_BRANCH_NAME') : $this->env->getString('CI_MERGE_REQUEST_TARGET_BRANCH_NAME');
    }
    public function getRepositoryName() : string
    {
        return $this->env->getString('CI_PROJECT_PATH');
    }
    public function getRepositoryUrl() : string
    {
        return !empty($this->env->getString('CI_REPOSITORY_URL')) ? $this->env->getString('CI_REPOSITORY_URL') : $this->env->getString('CI_BUILD_REPO');
    }
}
