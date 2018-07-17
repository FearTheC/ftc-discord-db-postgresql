<?php declare(strict_types=1);

namespace FTC\Discord\Db\Postgresql;


use FTC\Discord\Model\Aggregate\GuildWebsitePermissionRepository as RepositoryInterface;
use FTC\Discord\Model\ValueObject\Snowflake\GuildId;
use FTC\Discord\Model\Aggregate\GuildWebsitePermission;
use FTC\Discord\Model\Collection\GuildWebsitePermissionCollection;
use FTC\Discord\Model\ValueObject\Snowflake\RoleId;
use FTC\Discord\Model\ValueObject\Permission;

class GuildWebsitePermissionRepository extends PostgresqlRepository implements RepositoryInterface
{

    const SELECT_GUILD_WEBSITE_PERMISSIONS = <<<'EOT'
SELECT guild_id, role_id, route_name 
FROM guilds_websites_permissions
WHERE guild_id = :guild_id;
EOT;

    const SELECT_PERMISSION = <<<'EOT'
SELECT guild_id, role_id, route_name
FROM guilds_websites_permissions
WHERE role_id = :role_id AND route_name = :route_name
EOT;

    const INSERT_GUILD_WEBSITE_PERMISSION = <<<'EOT'
INSERT INTO guilds_websites_permissions
VALUES (:guild_id, :role_id, :route_name)
ON CONFLICT DO NOTHING
EOT;

    const DELETE_GUILD_WEBSITE_PERMISSION = <<<'EOT'
DELETE FROM guilds_websites_permissions
WHERE role_id = :role_id AND route_name = :route_name
EOT;
    
    public function save(GuildWebsitePermission $permission)
    {
        $stmt = $this->persistence->prepare(self::INSERT_GUILD_WEBSITE_PERMISSION);
        $stmt->bindValue('guild_id', (string) $permission->getGuildId()->get(), \PDO::PARAM_INT);
        $stmt->bindValue('route_name', $permission->getRouteName(), \PDO::PARAM_STR);
        $stmt->bindValue('role_id', (string) $permission->getRoleId()->get(), \PDO::PARAM_INT);
        $stmt->execute();
    }
    
    public function remove(GuildWebsitePermission $permission) : bool
    {
        $stmt = $this->persistence->prepare(self::DELETE_GUILD_WEBSITE_PERMISSION);
        $stmt->bindValue('route_name', $permission->getRouteName(), \PDO::PARAM_STR);
        $stmt->bindValue('role_id', (string) $permission->getRoleId(), \PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    public function getGuildPermissions(GuildId $guildId) : GuildWebsitePermissionCollection
    {
        $stmt = $this->persistence->prepare(self::SELECT_GUILD_WEBSITE_PERMISSIONS);
        $stmt->bindValue('guild_id', (string) $guildId->get(), \PDO::PARAM_INT);
        $stmt->execute();
        
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?? [];
        
        $permissions = array_map(
            function ($permission)
            {
                return new GuildWebsitePermission(
                    GuildId::create($permission['guild_id']),
                    RoleId::create($permission['role_id']),
                    $permission['route_name']
                );
            },
            $data
        );
        
        return new GuildWebsitePermissionCollection(...$permissions);
    }

    
    public function getPermission(RoleId $roleId, string $routeName) : ?GuildWebsitePermission
    {
        $stmt = $this->persistence->prepare(self::SELECT_PERMISSION);
        $stmt->bindValue('route_name', $routeName, \PDO::PARAM_STR);
        $stmt->bindValue('role_id', (string) $roleId, \PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($data) {
            $permission = new GuildWebsitePermission(
                GuildId::create($data['guild_id']),
                RoleId::create($data['role_id']),
                $data['route_name']
                );
        }
        
        return $permission ?? null;
    }
}
