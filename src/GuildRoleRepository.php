<?php
namespace FTC\Discord\Db\Postgresql;


use FTC\Discord\Model\Aggregate\GuildRoleRepository as RepositoryInterface;
use FTC\Discord\Model\Aggregate\GuildRole;
use FTC\Discord\Model\ValueObject\Snowflake\RoleId;
use FTC\Discord\Model\ValueObject\Snowflake\GuildId;
use FTC\Discord\Model\ValueObject\Name\RoleName;
use FTC\Discord\Model\Collection\GuildRoleCollection;

class GuildRoleRepository extends PostgresqlRepository implements RepositoryInterface
{
    
    const GET_BY_NAME = 'SELECT id, name, guild_id FROM guilds_roles WHERE guilds_roles.guild_id = :guild_id AND guilds_roles.name = :name';
    
    
    const INSERT_GUILD_ROLE = <<<'EOT'
INSERT INTO guilds_roles VALUES (:id, :guild_id, :name, :color, :position, :permissions, :mentionable, :hoist)
ON CONFLICT (id) DO UPDATE SET name = :name, color=:color, position=:position, permissions=:permissions, is_hoisted=:hoist, is_mentionable=:mentionable
EOT;

    const SELECT_GUILD_ROLES = "SELECT name FROM guilds_roles where guild_id = :guild_id and name <> '@everyone'";
    
    /**
     * @var GuildRole[]
     */
    private $guilds;
    
    public function save(GuildRole $role, GuildId $guildId)
    {
        $stmt = $this->persistence->prepare(self::INSERT_GUILD_ROLE);
        $stmt->bindValue('guild_id', $guildId->get(), \PDO::PARAM_INT);
        $stmt->bindValue('id', $role->getId()->get(), \PDO::PARAM_INT);
        $stmt->bindValue('name', $role->getName(), \PDO::PARAM_STR);
        $stmt->bindValue('color', $role->getColor()->getInteger(), \PDO::PARAM_INT);
        $stmt->bindValue('position', $role->getPosition(), \PDO::PARAM_INT);
        $stmt->bindValue('permissions', $role->getPermissions(), \PDO::PARAM_INT);
        $stmt->bindValue('mentionable', $role->isMentionable(), \PDO::PARAM_BOOL);
        $stmt->bindValue('hoist', $role->isHoisted(), \PDO::PARAM_BOOL);
        $stmt->execute();
        
    }
    
    public function getAll() : GuildRoleCollection
    {
        
    }
    
    public function findById(RoleId $id) : GuildRole
    {
        
    }
    
    public function findByName(RoleName $name, GuildId $guildId) : ?GuildRole
    {
        $stmt= $this->persistence->prepare(self::GET_BY_NAME);
        $stmt->bindParam('guild_id', $guildId, \PDO::PARAM_INT);
        $stmt->bindValue('name', $name, \PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($data == null) return null;
        
        return GuildRole::fromDbRow($data);
    }
    
    public function getAvailableRoles(GuildId $guildId)
    {
        $stmt= $this->persistence->prepare(self::SELECT_GUILD_ROLES);
        $stmt->bindParam(':guild_id', $guildId, \PDO::PARAM_STR);
        $stmt->execute();
        
        $results = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        
    }
    
}
