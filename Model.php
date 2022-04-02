<?php

namespace Webman;

use Jenssegers\Mongodb\Eloquent\Model as BaseModel;

//use Webman\Mongodb\Builder;

class Model extends BaseModel
{
//    /**
//     * @inheritdoc
//     ********************************************
//     * @return Builder
//     */
//    protected function newBaseQueryBuilder(): Builder
//    {
//        $connection = $this->getConnection();
//
//        return new Builder($connection, $connection->getPostProcessor());
//    }
}