<?php
declare(strict_types=1);

namespace FTC\Discord\Db\Postgresql;

use FTC\Discord\Model\GuildMemberRepository as RepositoryInterface;
use FTC\Discord\Model\GuildMember;
use FTC\Discord\Model\GuildRole;
use FTC\Discord\Model\Collection\GuildMemberCollection;
use FTC\Discord\Model\ValueObject\Snowflake\UserId;
use FTC\Discord\Model\ValueObject\Snowflake\RoleId;
use FTC\Discord\Model\ValueObject\Snowflake\GuildId;

class GuildMemberRepository extends PostgresqlRepository implements RepositoryInterface
{
    
    const GET_ALL_QUERY = <<<'EOT'
SELECT users.id, users.username, JSONB_AGG(guilds_roles) as roles
FROM users
JOIN users_roles ON users_roles.user_id = users.id
JOIN guilds_roles on guilds_roles.id = users_roles.role_id
GROUP BY users.id, users.username
EOT;
    
    const ADD_USER_Q = "INSERT INTO users VALUES (:user_id, :username) ON CONFLICT ON CONSTRAINT users_pkey DO NOTHING";
    
    const ADD_GUILD_USER_Q = "INSERT INTO guilds_users VALUES (:guild_id, :user_id)";
    
    const ADD_USER_ROLE = "INSERT INTO users_roles VALUES (:user_id, role)";
    
    const USER_QUERY = <<<'EOT'
select users.id, users.username, jsonb_agg(guilds_roles) as roles
from users
join users_roles on users_roles.user_id = users.id
join guilds_roles on guilds_roles.id = users_roles.role_id AND guilds_roles.guild_id = :guild_id
WHERE users.id = :user_id
group by users.id, users.username
EOT;
    
    /**
     * @var GuildMember[]
     */
    private $members;
    
    public function add(GuildMember $member)
    {
        $id = $member->getId();
        $username = $member->getUsername();
        
        $q = $this->persistence->prepare(self::ADD_USER_Q);
        $q->bindValue('user_id', $member->getId(), \PDO::PARAM_INT);
        $q->bindValue('username', $member->getUsername(), \PDO::PARAM_STR);
        $q->execute();
    }
    
    public function addGuild(GuildMember $member, int $guildId)
    {
        $q = $this->persistence->prepare(self::ADD_GUILD_USER_Q);
        $q->bindParam('guild_id', $guildId, \PDO::PARAM_INT);
        $q->bindValue('user_id', $member->getId(), \PDO::PARAM_INT);
        $q->execute();
    }
    
    public function addRole(GuildMember $memberId, GuildRole $roleName)
    {
        $q = $this->persistence->prepare(self::ADD_USER_ROLE);
        $q->bindValue('user_id', $member->getId(), \PDO::PARAM_INT);
        $q->bindParam('role_id', $role->getId(), \PDO::PARAM_INT);
        $q->execute();
    }
    
    public function remove(GuildMember $member)
    {
        
    }
    
    public function getAll() : GuildMemberCollection
    {
        $stmt = $this->persistence->prepare(self::GET_ALL_QUERY);
        $stmt->execute();
        $array =  $stmt->fetchAll(\PDO::FETCH_ASSOC);   
        $coll = $this->fromArray($array, $coll);
        
        return $coll;
    }
    
    private function fromArray($array)
    {
        $array = array_map([GuildMember::class, 'fromDb'], $array);
        return new GuildMemberCollection(...$array);

        return $collee;
    }
    
    public function findById(UserId $id) : GuildMember
    {
    }

    
}
