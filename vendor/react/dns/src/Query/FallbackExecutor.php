<?php

namespace RectorPrefix20220501\React\Dns\Query;

use RectorPrefix20220501\React\Promise\Promise;
final class FallbackExecutor implements \RectorPrefix20220501\React\Dns\Query\ExecutorInterface
{
    private $executor;
    private $fallback;
    public function __construct(\RectorPrefix20220501\React\Dns\Query\ExecutorInterface $executor, \RectorPrefix20220501\React\Dns\Query\ExecutorInterface $fallback)
    {
        $this->executor = $executor;
        $this->fallback = $fallback;
    }
    public function query(\RectorPrefix20220501\React\Dns\Query\Query $query)
    {
        $cancelled = \false;
        $fallback = $this->fallback;
        $promise = $this->executor->query($query);
        return new \RectorPrefix20220501\React\Promise\Promise(function ($resolve, $reject) use(&$promise, $fallback, $query, &$cancelled) {
            $promise->then($resolve, function (\Exception $e1) use($fallback, $query, $resolve, $reject, &$cancelled, &$promise) {
                // reject if primary resolution rejected due to cancellation
                if ($cancelled) {
                    $reject($e1);
                    return;
                }
                // start fallback query if primary query rejected
                $promise = $fallback->query($query)->then($resolve, function (\Exception $e2) use($e1, $reject) {
                    $append = $e2->getMessage();
                    if (($pos = \strpos($append, ':')) !== \false) {
                        $append = \substr($append, $pos + 2);
                    }
                    // reject with combined error message if both queries fail
                    $reject(new \RuntimeException($e1->getMessage() . '. ' . $append));
                });
            });
        }, function () use(&$promise, &$cancelled) {
            // cancel pending query (primary or fallback)
            $cancelled = \true;
            $promise->cancel();
        });
    }
}
