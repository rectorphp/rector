<?php

declare (strict_types=1);
namespace RectorPrefix202301\OndraM\CiDetector\Ci;

use RectorPrefix202301\OndraM\CiDetector\CiDetector;
use RectorPrefix202301\OndraM\CiDetector\Env;
use RectorPrefix202301\OndraM\CiDetector\TrinaryLogic;
class SourceHut extends AbstractCi
{
    public static function isDetected(Env $env) : bool
    {
        return $env->getString('CI_NAME') === 'sourcehut';
    }
    public function getCiName() : string
    {
        return CiDetector::CI_SOURCEHUT;
    }
    public function isPullRequest() : TrinaryLogic
    {
        return TrinaryLogic::createFromBoolean($this->env->getString('BUILD_REASON') === 'patchset');
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
