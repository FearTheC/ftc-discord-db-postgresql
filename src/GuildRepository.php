<?php
namespace FTC\Discord\Db\Postgresql;


use FTC\Discord\Db\Postgresql\GuildRepository as RepositoryInterface;
use FTC\Discord\Model\Guild;

class GuildRepository extends PostgresqlRepository implements RepositoryInterface
{
    /**
     * @var Guild[]
     */
    private $guilds;
    
    public function save(Guild $member)
    {
        
    }
    
    public function getAll() : array
    {
        
    }
    
    public function findById(int $id) : Guild
    {
        
    }
    
}
