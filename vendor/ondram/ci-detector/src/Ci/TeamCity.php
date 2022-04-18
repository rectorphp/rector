<?php

declare (strict_types=1);
namespace RectorPrefix20220418\OndraM\CiDetector\Ci;

use RectorPrefix20220418\OndraM\CiDetector\CiDetector;
use RectorPrefix20220418\OndraM\CiDetector\Env;
use RectorPrefix20220418\OndraM\CiDetector\TrinaryLogic;
class TeamCity extends \RectorPrefix20220418\OndraM\CiDetector\Ci\AbstractCi
{
    public static function isDetected(\RectorPrefix20220418\OndraM\CiDetector\Env $env) : bool
    {
        return $env->get('TEAMCITY_VERSION') !== \false;
    }
    public function getCiName() : string
    {
        return \RectorPrefix20220418\OndraM\CiDetector\CiDetector::CI_TEAMCITY;
    }
    public function isPullRequest() : \RectorPrefix20220418\OndraM\CiDetector\TrinaryLogic
    {
        return \RectorPrefix20220418\OndraM\CiDetector\TrinaryLogic::createMaybe();
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
