<?php declare(strict_types=1);

namespace FTC\Discord\Db\Postgresql\Mapper;

class GuildMemberMapper
{
    
    
    
    public static function create(array $data) : ?GuildMember
    {
        var_dump($data);
//         return GuildMember::create(
            
//             UserId::create($data['user_id']),
//             , $joinedAt);
    }
    
}
