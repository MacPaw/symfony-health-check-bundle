<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Tests\Mock;

class AbstractPlatformMock
{
    public function getDummySelectSQL(): string
    {
        $expression = func_num_args() > 0 ? func_get_arg(0) : '1';

        return sprintf('SELECT %s', $expression);
    }
}
