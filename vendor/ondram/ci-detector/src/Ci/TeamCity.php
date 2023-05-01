<?php

declare (strict_types=1);
namespace RectorPrefix202305\OndraM\CiDetector\Ci;

use RectorPrefix202305\OndraM\CiDetector\CiDetector;
use RectorPrefix202305\OndraM\CiDetector\Env;
use RectorPrefix202305\OndraM\CiDetector\TrinaryLogic;
class TeamCity extends AbstractCi
{
    public static function isDetected(Env $env) : bool
    {
        return $env->get('TEAMCITY_VERSION') !== \false;
    }
    public function getCiName() : string
    {
        return CiDetector::CI_TEAMCITY;
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
        return '';
        // unsupported
    }
    public function getCommit() : string
    {
        return $this->env->getString('BUILD_VCS_NUMBER');
    }
    public function getBranch() : string
    {
        return '';
        // unsupported
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
        return '';
        // unsupported
    }
}
