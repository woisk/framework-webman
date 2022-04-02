<?php

namespace Webman\Mongodb;

use Jenssegers\Mongodb\Connection as JenssegersConnection;


/**
 * Connection
 * Author: Maple Grove woisks@126.com
 ***/
class Connection extends JenssegersConnection
{

    protected $session;



    public function beginTransaction(array $options = [])
    {
        if (!$this->getSession()) {
            $this->session = $this->getMongoClient()->startSession();
            $this->session->startTransaction($options);
        }
    }



    public function commit()
    {
        if ($this->getSession()) {
            $this->session->commitTransaction();
            $this->clearSession();
        }
    }



    public function rollBack($toLevel = null)
    {
        if ($this->getSession()) {
            $this->session->abortTransaction();
            $this->clearSession();
        }
    }



    protected function clearSession()
    {
        $this->session = null;
    }



    public function collection($collection)
    {
        $query = new Builder($this, $this->getPostProcessor());

        return $query->from($collection);
    }


    public function getSession()
    {
        return $this->session;
    }
}
