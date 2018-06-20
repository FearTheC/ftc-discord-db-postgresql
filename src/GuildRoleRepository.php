<?php
namespace FTCBotCore\Discord\Repository;


use FTC\Discord\Db\PostgresqlGuildRoleRepository as RepositoryInterface;
use FTC\Discord\Db\PostgresqlGuildRole;

class GuildRoleRepository extends PostgresqlRepository implements RepositoryInterface
{
    
    const GET_BY_NAME = 'SELECT id, name, guild_id FROM guilds_roles WHERE guilds_roles.guild_id = :guild_id AND guilds_roles.name = :name';
    
    /**
     * @var GuildRole[]
     */
    private $guilds;
    
    public function save(GuildRole $member)
    {
        
    }
    
    public function getAll() : array
    {
        
    }
    
    public function findById(int $id) : GuildRole
    {
        
    }
    
    public function findByName(string $name, int $guildId) : ?GuildRole
    {
        $q = $this->persistence->prepare(self::GET_BY_NAME);
        $q->bindParam('guild_id', $guildId, \PDO::PARAM_INT);
        $q->bindValue('name', $name, \PDO::PARAM_STR);
        $q->execute();
        $data = $q->fetch(\PDO::FETCH_ASSOC);

        if ($data == null) return null;
        
        return GuildRole::fromDbRow($data);
    }
    
}
