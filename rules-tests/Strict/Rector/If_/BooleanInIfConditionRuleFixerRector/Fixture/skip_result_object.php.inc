<?php

/**
 * FUTURE NOTE: Dont add namespace to this Fixture:
 *
 * Skip Result Object or int only reproducible when on non-namespaced class
 * and collection of class is on single file
 */
class ForwardCompatibilityResult {

}

class QueryBuilder {
    /**
     * Executes this query using the bound parameters and their types.
     *
     * @return ForwardCompatibilityResult|int
     *
     * @throws Exception
     */
    public function execute()
    {
        if ($this->type === self::SELECT) {
            return new ForwardCompatibilityResult();
        }

        return 1;
    }
}

class SkipResultObject
{
    public function insert(): bool {
        $queryBuilder = new QueryBuilder();
        if ($queryBuilder->execute()) {
            return false;
        }

        throw new \RuntimeException('...');
    }
}
