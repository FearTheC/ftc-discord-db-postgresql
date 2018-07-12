<?php declare(strict_types=1);

namespace FTC\Discord\Db\Postgresql\Mapper;

use FTC\Discord\Model\ValueObject\Snowflake\RoleId;
use FTC\Discord\Model\Aggregate\GuildRole;
use FTC\Discord\Model\ValueObject\Name\RoleName;
use FTC\Discord\Model\ValueObject\Color;
use FTC\Discord\Model\ValueObject\Permission;

class GuildRoleMapper
{
    
    public static function create(array $data) : GuildRole
    {
        $role = GuildRole::create(
            RoleId::create($data['id']),
            RoleName::create($data['name']),
            Color::createFromInteger($data['color']),
            new Permission($data['permissions']),
            $data['position'],
            $data['is_mentionable'],
            $data['is_hoisted']
        );
        
        return $role;
    }
    
}
