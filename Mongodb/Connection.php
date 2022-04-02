<?php

namespace Webman\Mongodb;

use Jenssegers\Mongodb\Connection as JenssegersConnection;
use MongoDB\Driver\Session;

/**
 * Connection
 * Author: Maple Grove woisks@126.com
 ***/
class Connection extends JenssegersConnection
{
    /**
     * @var Session
     */
    protected Session $session;


    /**
     * @param array $options
     ********************************************
     * @return void
     */
    public function beginTransaction(array $options = [])
    {
        if (!$this->getSession()) {
            $this->session = $this->getMongoClient()->startSession();
            $this->session->startTransaction($options);
        }
    }


    /**
     ********************************************
     * @return void
     */
    public function commit()
    {
        if ($this->getSession()) {
            $this->session->commitTransaction();
            $this->clearSession();
        }
    }


    /**
     * @param $toLevel
     ********************************************
     * @return void
     */
    public function rollBack($toLevel = null)
    {
        if ($this->getSession()) {
            $this->session->abortTransaction();
            $this->clearSession();
        }
    }


    /**
     ********************************************
     * @return void
     */
    protected function clearSession()
    {
        $this->session = null;
    }


    /**
     * @param $collection
     ********************************************
     * @return \Jenssegers\Mongodb\Query\Builder|\Webman\Mongodb\Builder
     */
    public function collection($collection): \Jenssegers\Mongodb\Query\Builder|\Webman\Mongodb\Builder
    {
        $query = new Builder($this, $this->getPostProcessor());

        return $query->from($collection);
    }

    /**
     ********************************************
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }
}
