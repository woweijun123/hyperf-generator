<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Riven;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

abstract class BaseValidator
{
    #[Inject]
    public ValidatorFactoryInterface $interface;

    /**
     * @param array $data 验证的数据
     * @param array $rule 验证的规则
     * @param array $msg  验证的自定义提示
     */
    protected function verify(array $data, array $rule, array $msg = []): array
    {
        return $this->interface->make($data, $rule, $msg)->validate();
    }
}
