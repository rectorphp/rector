<?php

declare (strict_types=1);
namespace RectorPrefix202304\OndraM\CiDetector\Ci;

use RectorPrefix202304\OndraM\CiDetector\CiDetector;
use RectorPrefix202304\OndraM\CiDetector\Env;
use RectorPrefix202304\OndraM\CiDetector\TrinaryLogic;
class AppVeyor extends AbstractCi
{
    public static function isDetected(Env $env) : bool
    {
        return $env->get('APPVEYOR') === 'True';
    }
    public function getCiName() : string
    {
        return CiDetector::CI_APPVEYOR;
    }
    public function isPullRequest() : TrinaryLogic
    {
        return TrinaryLogic::createFromBoolean($this->env->getString('APPVEYOR_PULL_REQUEST_NUMBER') !== '');
    }
    public function getBuildNumber() : string
    {
        return $this->env->getString('APPVEYOR_BUILD_NUMBER');
    }
    public function getBuildUrl() : string
    {
        return \sprintf('%s/project/%s/%s/builds/%s', $this->env->get('APPVEYOR_URL'), $this->env->get('APPVEYOR_ACCOUNT_NAME'), $this->env->get('APPVEYOR_PROJECT_SLUG'), $this->env->get('APPVEYOR_BUILD_ID'));
    }
    public function getCommit() : string
    {
        return $this->env->getString('APPVEYOR_REPO_COMMIT');
    }
    public function getBranch() : string
    {
        $prBranch = $this->env->getString('APPVEYOR_PULL_REQUEST_HEAD_REPO_BRANCH');
        if ($this->isPullRequest()->no() || empty($prBranch)) {
            return $this->env->getString('APPVEYOR_REPO_BRANCH');
        }
        return $prBranch;
    }
    public function getTargetBranch() : string
    {
        if ($this->isPullRequest()->no()) {
            return '';
        }
        return $this->env->getString('APPVEYOR_REPO_BRANCH');
    }
    public function getRepositoryName() : string
    {
        return $this->env->getString('APPVEYOR_REPO_NAME');
    }
    public function getRepositoryUrl() : string
    {
        return '';
        // unsupported
    }
}
