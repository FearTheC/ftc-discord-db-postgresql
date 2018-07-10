<?php declare(strict_types=1);

namespace FTC\Discord\Db\Postgresql\Container;

use Psr\Container\ContainerInterface;
use FTC\Discord\Db\Postgresql\GuildChannelRepository as GuildRepositoryImp;
use FTC\Discord\Db\Core;

class GuildChannelRepository
{
    
    public function __invoke(ContainerInterface $container)
    {
        $database = $container->get(Core::class);

        return new GuildRepositoryImp($database);
    }
    
}
