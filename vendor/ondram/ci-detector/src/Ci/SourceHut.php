<?php

declare (strict_types=1);
namespace RectorPrefix20220531\OndraM\CiDetector\Ci;

use RectorPrefix20220531\OndraM\CiDetector\CiDetector;
use RectorPrefix20220531\OndraM\CiDetector\Env;
use RectorPrefix20220531\OndraM\CiDetector\TrinaryLogic;
class SourceHut extends \RectorPrefix20220531\OndraM\CiDetector\Ci\AbstractCi
{
    public static function isDetected(\RectorPrefix20220531\OndraM\CiDetector\Env $env) : bool
    {
        return $env->getString('CI_NAME') === 'sourcehut';
    }
    public function getCiName() : string
    {
        return \RectorPrefix20220531\OndraM\CiDetector\CiDetector::CI_SOURCEHUT;
    }
    public function isPullRequest() : \RectorPrefix20220531\OndraM\CiDetector\TrinaryLogic
    {
        return \RectorPrefix20220531\OndraM\CiDetector\TrinaryLogic::createFromBoolean($this->env->getString('BUILD_REASON') === 'patchset');
    }
    public function getBuildNumber() : string
    {
        return $this->env->getString('JOB_ID');
    }
    public function getBuildUrl() : string
    {
        return $this->env->getString('JOB_URL');
    }
    public function getCommit() : string
    {
        return '';
        // unsupported
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
