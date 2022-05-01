<?php

declare (strict_types=1);
namespace RectorPrefix20220501\OndraM\CiDetector\Ci;

use RectorPrefix20220501\OndraM\CiDetector\CiDetector;
use RectorPrefix20220501\OndraM\CiDetector\Env;
use RectorPrefix20220501\OndraM\CiDetector\TrinaryLogic;
class Codeship extends \RectorPrefix20220501\OndraM\CiDetector\Ci\AbstractCi
{
    public static function isDetected(\RectorPrefix20220501\OndraM\CiDetector\Env $env) : bool
    {
        return $env->get('CI_NAME') === 'codeship';
    }
    public function getCiName() : string
    {
        return \RectorPrefix20220501\OndraM\CiDetector\CiDetector::CI_CODESHIP;
    }
    public function isPullRequest() : \RectorPrefix20220501\OndraM\CiDetector\TrinaryLogic
    {
        return \RectorPrefix20220501\OndraM\CiDetector\TrinaryLogic::createFromBoolean($this->env->getString('CI_PULL_REQUEST') !== 'false');
    }
    public function getBuildNumber() : string
    {
        return $this->env->getString('CI_BUILD_NUMBER');
    }
    public function getBuildUrl() : string
    {
        return $this->env->getString('CI_BUILD_URL');
    }
    public function getCommit() : string
    {
        return $this->env->getString('COMMIT_ID');
    }
    public function getBranch() : string
    {
        return $this->env->getString('CI_BRANCH');
    }
    public function getTargetBranch() : string
    {
        return '';
        // unsupported
    }
    public function getRepositoryName() : string
    {
        return $this->env->getString('CI_REPO_NAME');
    }
    public function getRepositoryUrl() : string
    {
        return '';
        // unsupported
    }
}
