<?php

declare (strict_types=1);
namespace RectorPrefix202411\OndraM\CiDetector\Ci;

use RectorPrefix202411\OndraM\CiDetector\CiDetector;
use RectorPrefix202411\OndraM\CiDetector\Env;
use RectorPrefix202411\OndraM\CiDetector\TrinaryLogic;
class AwsCodeBuild extends AbstractCi
{
    public static function isDetected(Env $env) : bool
    {
        return $env->get('CODEBUILD_CI') !== \false;
    }
    public function getCiName() : string
    {
        return CiDetector::CI_AWS_CODEBUILD;
    }
    public function isPullRequest() : TrinaryLogic
    {
        return TrinaryLogic::createFromBoolean(\mb_strpos($this->env->getString('CODEBUILD_WEBHOOK_EVENT'), 'PULL_REQUEST') === 0);
    }
    public function getBuildNumber() : string
    {
        return $this->env->getString('CODEBUILD_BUILD_NUMBER');
    }
    public function getBuildUrl() : string
    {
        return $this->env->getString('CODEBUILD_BUILD_URL');
    }
    public function getCommit() : string
    {
        return $this->env->getString('CODEBUILD_RESOLVED_SOURCE_VERSION');
    }
    public function getBranch() : string
    {
        $gitReference = $this->env->getString('CODEBUILD_WEBHOOK_HEAD_REF');
        return \preg_replace('~^refs/heads/~', '', $gitReference) ?? '';
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
        return $this->env->getString('CODEBUILD_SOURCE_REPO_URL');
    }
}
