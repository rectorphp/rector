<?php

declare (strict_types=1);
namespace Rector\Parallel\Experimental;

use Rector\Parallel\Experimental\ValueObject\BucketSchedule;
use RectorPrefix202610\Webmozart\Assert\Assert;
/**
 * @experimental Alternative to @see \Rector\Parallel\ScheduleFactory, used by "--lpt".
 *
 * The default scheduler cuts the file list into jobSize chunks and lets any worker pull any chunk. This
 * one splits the files into one fixed bucket per worker, balanced by total byte size, so a worker is
 * started once and keeps its warm caches for the whole run.
 *
 * Idea from https://github.com/rectorphp/rector-src/issues/8494#issuecomment-5664864017
 */
final class LptScheduleFactory
{
    /**
     * @param string[] $filePaths
     */
    public function create(int $cpuCores, int $jobSize, int $maxNumberOfProcesses, array $filePaths): BucketSchedule
    {
        Assert::positiveInteger($jobSize);
        Assert::notEmpty($filePaths);
        // same worker count as the default scheduler: a worker is worth starting once there is at least
        // one whole job for it
        $numberOfProcesses = min((int) ceil(count($filePaths) / $jobSize), $cpuCores, $maxNumberOfProcesses);
        $numberOfProcesses = max(1, $numberOfProcesses);
        $jobsPerWorker = [];
        foreach ($this->createBalancedBuckets($filePaths, $numberOfProcesses) as $bucketFilePaths) {
            // fewer files than workers - no worker for an empty bucket
            if ($bucketFilePaths === []) {
                continue;
            }
            // jobSize is what a worker is handed per request. Balancing already happened when the buckets
            // were built, so this is the heartbeat interval - a response advances the progress bar and
            // re-arms the per-job timeout - and, more importantly, the granularity at which an idle worker
            // can take work over from a busy one.
            $jobsPerWorker[] = array_chunk($bucketFilePaths, $jobSize);
        }
        return new BucketSchedule($jobsPerWorker);
    }
    /**
     * LPT (Longest Processing Time first): walk the files largest-first and drop each one into the bucket
     * with the smallest byte load so far. Keeps the gap between the slowest and the fastest worker small,
     * so the run does not end with a single worker still chewing on the one 200 kB file.
     *
     * @param string[] $filePaths
     * @return array<int, array<string>>
     */
    private function createBalancedBuckets(array $filePaths, int $numberOfBuckets): array
    {
        // tuples rather than a path-keyed map: numeric-looking paths would be cast to int keys, and
        // duplicate paths would silently collapse
        $sizedFilePaths = [];
        foreach ($filePaths as $filePath) {
            $sizedFilePaths[] = [$filePath, is_file($filePath) ? (int) filesize($filePath) : 0];
        }
        usort($sizedFilePaths, static fn(array $first, array $second): int => $second[1] <=> $first[1]);
        $buckets = array_fill(0, $numberOfBuckets, []);
        $bucketSizes = array_fill(0, $numberOfBuckets, 0);
        foreach ($sizedFilePaths as [$filePath, $fileSize]) {
            $emptiestBucketKey = 0;
            foreach ($bucketSizes as $currentBucketKey => $bucketSize) {
                if ($bucketSize < $bucketSizes[$emptiestBucketKey]) {
                    $emptiestBucketKey = $currentBucketKey;
                }
            }
            $buckets[$emptiestBucketKey][] = $filePath;
            $bucketSizes[$emptiestBucketKey] += $fileSize;
        }
        return $buckets;
    }
}
