<?php
namespace FTCBotCore\Discord\Repository;

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
