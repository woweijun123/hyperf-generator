<?php

declare(strict_types=1);

namespace Riven;

use Hyperf\Database\Model\Model;

abstract class BaseModel extends Model
{
    /**
     * 字段过滤器
     * @param $attributes
     * @return Model
     */
    public function optimizer(&$attributes): BaseModel
    {
        $attributes = array_intersect_key($attributes, array_flip($this->getFillable()));
        return $this;
    }
}
