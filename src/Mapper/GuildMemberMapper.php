<?php declare(strict_types=1);

namespace FTC\Discord\Db\Postgresql\Mapper;

use FTC\Discord\Model\Aggregate\GuildMember;
use FTC\Discord\Model\ValueObject\Snowflake\UserId;
use FTC\Discord\Model\ValueObject\Snowflake\RoleId;
use FTC\Discord\Model\Collection\GuildRoleIdCollection;
use FTC\Discord\Model\ValueObject\Name\NickName;

class GuildMemberMapper
{
    
    public static function create(array $data) : ?GuildMember
    {
//         var_dump($data);
//         var_dump(json_decode($data['roles']));
//         var_dump(json_decode($data['roles'], true));
        
        if ($data['roles']) {
            $rolesIds = array_map(function($value) { return $value['id']; }, json_decode($data['roles'], true));
        }
        
        $rolesIds = array_map([RoleId::class, 'create'], $rolesIds);
        var_dump($data);

        $rolesIdsColl = new GuildRoleIdCollection(...$rolesIds);
        return GuildMember::create(
            UserId::create($data['id']),
            $rolesIdsColl,
            new \DateTime($data['joined_date']),
            NickName::create($data['nickname'])
        );
    }
    
    
    private static function extractRolesIds(array $data)
    {
        return $data['id'];
    }
    
}
