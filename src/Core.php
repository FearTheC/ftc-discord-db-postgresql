<?php
namespace FTC\Discord\Db\Postgresql;

class Core
{
    
    private $connection;
    
    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }
    
}
