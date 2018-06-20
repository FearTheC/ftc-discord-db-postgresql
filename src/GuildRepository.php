<?php
namespace FTCBotCore\Discord\Repository;


use FTC\Discord\Db\PostgresqlGuildRepository as RepositoryInterface;
use FTC\Discord\Db\PostgresqlGuild;

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
