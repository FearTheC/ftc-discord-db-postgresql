<?php
namespace FTC\Discord\Db\Postgresql\Factory;

use Psr\Container\ContainerInterface;
use FTC\Discord\Db\Postgresql\GuildRepository as GuildRepositoryImp;
use FTC\Discord\Db\Core;

class GuildRepository
{
    
    public function __invoke(ContainerInterface $container)
    {
        $database = $container->get(Core::class);

        return new GuildRepositoryImp($database);
    }
    
}
