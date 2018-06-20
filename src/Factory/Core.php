<?php
namespace FTC\Discord\Db\Postgresql\Factory;

use Psr\Container\ContainerInterface;

class Core
{
    
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config')['core-db'];
        $dsn = sprintf("pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s",
            $config['host'],
            $config['port'],
            $config['database'],
            $config['user'],
            $config['password']);
        
        $pdo = new \PDO($dsn);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        return $pdo;
    }
    
}
