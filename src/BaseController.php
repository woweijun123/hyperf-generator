<?php

declare(strict_types=1);

namespace Riven;

use Hyperf\HttpServer\Contract\RequestInterface;

abstract class BaseController
{
    public function __construct(protected RequestInterface $request)
    {
    }


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
