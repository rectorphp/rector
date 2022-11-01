<?php

declare (strict_types=1);
namespace RectorPrefix202211\OndraM\CiDetector\Ci;

use RectorPrefix202211\OndraM\CiDetector\CiDetector;
use RectorPrefix202211\OndraM\CiDetector\Env;
use RectorPrefix202211\OndraM\CiDetector\TrinaryLogic;
class Jenkins extends AbstractCi
{
    public static function isDetected(Env $env) : bool
    {
        return $env->get('JENKINS_URL') !== \false;
    }
    public function getCiName() : string
    {
        return CiDetector::CI_JENKINS;
    }
    public function isPullRequest() : TrinaryLogic
    {
        return TrinaryLogic::createMaybe();
    }
    public function getBuildNumber() : string
    {
        return $this->env->getString('BUILD_NUMBER');
    }
    public function getBuildUrl() : string
    {
        return $this->env->getString('BUILD_URL');
    }
    public function getCommit() : string
    {
        return $this->env->getString('GIT_COMMIT');
    }
    public function getBranch() : string
    {
        return $this->env->getString('GIT_BRANCH');
    }
    public function getTargetBranch() : string
    {
        return '';
        // unsupported
    }
    public function getRepositoryName() : string
    {
        return '';
        // unsupported
    }
    public function getRepositoryUrl() : string
    {
        return $this->env->getString('GIT_URL');
    }
}
