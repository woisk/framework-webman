<?php

namespace Webman\Mongodb;

use Illuminate\Support\Str;
use Jenssegers\Mongodb\Query\Builder as JenssegersBuilder;
use Jenssegers\Mongodb\Query\Grammar;
use Jenssegers\Mongodb\Query\Processor;

/**
 * Builder
 * Author: Maple Grove woisks@126.com
 ***/
class Builder extends JenssegersBuilder
{

    /**
     * @param \Webman\Mongodb\Connection $connection
     * @param Processor $processor
     */
    public function __construct(Connection $connection, Processor $processor)
    {
        $this->grammar = new Grammar;
        $this->connection = $connection;
        $this->processor = $processor;
        $this->useCollections = $this->shouldUseCollections();
    }


    /**
     * @param $function
     * @param $columns
     ********************************************
     * @return mixed|void
     */
    public function aggregate($function, $columns = [])
    {
        $this->aggregate = compact('function', 'columns');

        $previousColumns = $this->columns;

        // We will also back up the select bindings since the select clause will be
        // removed when performing the aggregate function. Once the query is run
        // we will add the bindings back onto this query so they can get used.
        $previousSelectBindings = $this->bindings['select'];

        $this->bindings['select'] = [];

        $results = $this->get($columns);

        // Once we have executed the query, we will reset the aggregate property so
        // that more select queries can be executed against the database without
        // the aggregate value getting in the way when the grammar builds it.
        $this->aggregate = null;
        $this->columns = $previousColumns;
        $this->bindings['select'] = $previousSelectBindings;

        if (isset($results[0])) {
            $result = (array)$results[0];

            return $result['aggregate'];
        }
    }


    /**
     * @param array $values
     ********************************************
     * @return bool
     */
    public function insert(array $values): bool
    {
        // Since every insert gets treated like a batch insert, we will have to detect
        // if the user is inserting a single document or an array of documents.
        $batch = true;

        foreach ($values as $value) {
            // As soon as we find a value that is not an array we assume the user is
            // inserting a single document.
            if (!is_array($value)) {
                $batch = false;
                break;
            }
        }

        if (!$batch) {
            $values = [$values];
        }

        // Batch insert
        $options = $this->session();
        $result = $this->collection->insertMany($values, $options);

        return (1 == (int)$result->isAcknowledged());
    }


    /**
     * @param array $values
     * @param $sequence
     ********************************************
     * @return int|mixed|void
     */
    public function insertGetId(array $values, $sequence = null)
    {
        $options = $this->session();
        $result = $this->collection->insertOne($values, $options);

        if (1 == (int)$result->isAcknowledged()) {
            if (is_null($sequence)) {
                $sequence = '_id';
            }

            // Return id
            return $sequence == '_id' ? $result->getInsertedId() : $values[$sequence];
        }
    }


    /**
     * @param array $values
     * @param array $options
     ********************************************
     * @return int
     */
    public function update(array $values, array $options = []): int
    {
        // Use $set as default operator.
        if (!Str::startsWith(key($values), '$')) {
            $values = ['$set' => $values];
        }
        $options = $this->session($options);
        return $this->performUpdate($values, $options);
    }


    /**
     * @param $column
     * @param $amount
     * @param array $extra
     * @param array $options
     ********************************************
     * @return int
     */
    public function increment($column, $amount = 1, array $extra = [], array $options = []): int
    {
        $query = ['$inc' => [$column => $amount]];

        if (!empty($extra)) {
            $query['$set'] = $extra;
        }

        // Protect
        $this->where(function ($query) use ($column) {
            $query->where($column, 'exists', false);

            $query->orWhereNotNull($column);
        });

        return $this->performUpdate($query, $options);
    }


    /**
     * @param $column
     * @param $amount
     * @param array $extra
     * @param array $options
     ********************************************
     * @return int
     */
    public function decrement($column, $amount = 1, array $extra = [], array $options = []): int
    {
        return $this->increment($column, -1 * $amount, $extra, $options);
    }


    /**
     * @param $id
     ********************************************
     * @return int
     */
    public function delete($id = null): int
    {
        // If an ID is passed to the method, we will set the where clause to check
        // the ID to allow developers to simply and quickly remove a single row
        // from their database without manually specifying the where clauses.
        if (!is_null($id)) {
            $this->where('_id', '=', $id);
        }

        $options = $this->session();
        $wheres = $this->compileWheres();
        $result = $this->collection->DeleteMany($wheres, $options);
        if (1 == (int)$result->isAcknowledged()) {
            return $result->getDeletedCount();
        }

        return 0;
    }


    /**
     ********************************************
     * @return bool
     */
    public function truncate(): bool
    {
        $result = $this->collection->drop();

        return (1 == (int)$result->ok);
    }

    /**
     * Perform an update query.
     *
     * @param array $query
     * @param array $options
     * @return int
     */
    protected function performUpdate($query, array $options = []): int
    {
        // Update multiple items by default.
        if (!array_key_exists('multiple', $options)) {
            $options['multiple'] = true;
        }
        $options = $this->session($options);

        $wheres = $this->compileWheres();
        $result = $this->collection->UpdateMany($wheres, $query, $options);
        if (1 == (int)$result->isAcknowledged()) {
            return $result->getModifiedCount() ? $result->getModifiedCount() : $result->getUpsertedCount();
        }

        return 0;
    }

    /**
     * @param array $options
     ********************************************
     * @return array
     */
    protected function session(array $options = []): array
    {
        if ($session = $this->connection->getSession()) {
            $options['session'] = $this->connection->getSession();
        }

        return $options;
    }
}