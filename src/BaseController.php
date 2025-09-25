<?php

declare(strict_types=1);

namespace Riven;

abstract class BaseController
{
    /**
     * 响应「成功」
     * @param array  $data
     * @param string $msg
     * @return array
     */
    public static function success(array $data = [], string $msg = 'success'): array
    {
        return [
            'code' => 200,
            'msg'  => $msg,
            'data' => $data,
        ];
    }
}
