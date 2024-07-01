<?php

declare (strict_types=1);
namespace RectorPrefix202407\OndraM\CiDetector\Ci;

use RectorPrefix202407\OndraM\CiDetector\CiDetector;
use RectorPrefix202407\OndraM\CiDetector\Env;
use RectorPrefix202407\OndraM\CiDetector\TrinaryLogic;
class Drone extends AbstractCi
{
    public static function isDetected(Env $env) : bool
    {
        return $env->get('CI') === 'drone';
    }
    public function getCiName() : string
    {
        return CiDetector::CI_DRONE;
    }
    public function isPullRequest() : TrinaryLogic
    {
        return TrinaryLogic::createFromBoolean($this->env->getString('DRONE_PULL_REQUEST') !== '');
    }
    public function getBuildNumber() : string
    {
        return $this->env->getString('DRONE_BUILD_NUMBER');
    }
    public function getBuildUrl() : string
    {
        return $this->env->getString('DRONE_BUILD_LINK');
    }
    public function getCommit() : string
    {
        return $this->env->getString('DRONE_COMMIT_SHA');
    }
    public function getBranch() : string
    {
        return $this->env->getString('DRONE_COMMIT_BRANCH');
    }
    public function getTargetBranch() : string
    {
        return $this->env->getString('DRONE_TARGET_BRANCH');
    }
    public function getRepositoryName() : string
    {
        return $this->env->getString('DRONE_REPO');
    }
    public function getRepositoryUrl() : string
    {
        return $this->env->getString('DRONE_REPO_LINK');
    }
}
