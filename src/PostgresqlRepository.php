<?php
namespace FTC\Discord\Db\Postgresql;

abstract class PostgresqlRepository
{
    
    /**
     * @var \PDO
     */
    protected $persistence;
    
    public function __construct(\PDO $persistence)
    {
        $this->persistence = $persistence;
    }
    
}
