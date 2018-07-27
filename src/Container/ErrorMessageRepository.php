<?php

declare(strict_types=1);

namespace FTC\Discord\Db\Postgresql\Container;

use Psr\Container\ContainerInterface;
use FTC\Discord\Db\Postgresql\ErrorMessageRepository as ErrorMessageRepositoryImp;
use FTC\Discord\Db\Core;

class ErrorMessageRepository
{
    
    public function __invoke(ContainerInterface $container)
    {
        $database = $container->get(Core::class);

        return new ErrorMessageRepositoryImp($database);
    }
    
}
