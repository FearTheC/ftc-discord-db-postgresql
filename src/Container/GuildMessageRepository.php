<?php declare(strict_types=1);

namespace FTC\Discord\Db\Postgresql\Container;

use Psr\Container\ContainerInterface;
use FTC\Discord\Db\Postgresql\GuildMessageRepository as GuildMessageRepositoryImp;
use FTC\Discord\Db\Core;

class GuildMessageRepository
{
    
    public function __invoke(ContainerInterface $container)
    {
        $database = $container->get(Core::class);

        return new GuildMessageRepositoryImp($database);
    }
    
}
