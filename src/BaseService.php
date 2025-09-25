<?php

namespace Riven;

use Hyperf\Contract\IdGeneratorInterface;
use Hyperf\Di\Annotation\Inject;

class BaseService
{
    #[Inject]
    protected IdGeneratorInterface $snowflake;
}