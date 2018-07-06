<?php
namespace FTC\Discord\Db\Postgresql;


use FTC\Discord\Model\Aggregate\GuildRoleRepository as RepositoryInterface;
use FTC\Discord\Model\Aggregate\GuildRole;
use FTC\Discord\Model\ValueObject\Snowflake\RoleId;
use FTC\Discord\Model\ValueObject\Snowflake\GuildId;
use FTC\Discord\Model\ValueObject\Name\RoleName;
use FTC\Discord\Model\Collection\GuildRoleIdCollection;

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
    
    public function getAll() : GuildRoleIdCollection
    {
        
    }
    
    public function findById(RoleId $id) : GuildRole
    {
        
    }
    
    public function findByName(RoleName $name, GuildId $guildId) : ?GuildRole
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
