<?php

declare (strict_types=1);
namespace RectorPrefix202407\OndraM\CiDetector\Ci;

use RectorPrefix202407\OndraM\CiDetector\CiDetector;
use RectorPrefix202407\OndraM\CiDetector\Env;
use RectorPrefix202407\OndraM\CiDetector\TrinaryLogic;
class Travis extends AbstractCi
{
    public static function isDetected(Env $env) : bool
    {
        return $env->get('TRAVIS') !== \false;
    }
    public function getCiName() : string
    {
        return CiDetector::CI_TRAVIS;
    }
    public function isPullRequest() : TrinaryLogic
    {
        return TrinaryLogic::createFromBoolean($this->env->getString('TRAVIS_PULL_REQUEST') !== 'false');
    }
    public function getBuildNumber() : string
    {
        return $this->env->getString('TRAVIS_JOB_NUMBER');
    }
    public function getBuildUrl() : string
    {
        return $this->env->getString('TRAVIS_JOB_WEB_URL');
    }
    public function getCommit() : string
    {
        return $this->env->getString('TRAVIS_COMMIT');
    }
    public function getBranch() : string
    {
        if ($this->isPullRequest()->no()) {
            return $this->env->getString('TRAVIS_BRANCH');
        }
        // If the build is for PR, return name of the branch with the PR, not the target PR branch
        // https://github.com/travis-ci/travis-ci/issues/6652
        return $this->env->getString('TRAVIS_PULL_REQUEST_BRANCH');
    }
    public function getTargetBranch() : string
    {
        if ($this->isPullRequest()->no()) {
            return '';
        }
        return $this->env->getString('TRAVIS_BRANCH');
    }
    public function getRepositoryName() : string
    {
        return $this->env->getString('TRAVIS_REPO_SLUG');
    }
    public function getRepositoryUrl() : string
    {
        return '';
        // unsupported
    }
}
