<?php
namespace FTC\Discord\Db\Postgresql;

use FTC\Discord\Model\GuildMemberRepository as RepositoryInterface;
use FTC\Discord\Model\GuildMember;

class GuildMemberRepository extends PostgresqlRepository implements RepositoryInterface
{
    
    const GET_ALL_QUERY = 'SELECT id, username FROM users ORDER BY id DESC';
    
    const ADD_USER_Q = "INSERT INTO users VALUES (:user_id, :username) ON CONFLICT ON CONSTRAINT users_pkey DO NOTHING";
    
    const ADD_GUILD_USER_Q = "INSERT INTO guilds_users VALUES (:guild_id, :user_id)";
    
    const ADD_USER_ROLE = "INSERT INTO users_roles VALUES (:user_id, role)";
    
    const USER_QUERY = <<<'EOT'
select users.id, users.username, json_agg(guilds_roles.*) as roles
from users
LEFT join users_roles on users_roles.user_id = users.id
LEFT join guilds_roles on guilds_roles.id = users_roles.role_id AND guilds_roles.guild_id = :guild_id
WHERE users.id = :user_id
group by users.id
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
    
    public function addRole(int $memberId, int $roleName)
    {
        $q = $this->persistence->prepare(self::ADD_USER_ROLE);
        $q->bindValue('user_id', $member->getId(), \PDO::PARAM_INT);
        $q->bindParam('role_id', $role->getId(), \PDO::PARAM_INT);
        $q->execute();
    }
    
    public function remove(GuildMember $member)
    {
        
    }
    
    public function getAll() : array
    {
        $stmt = $this->persistence->prepare(self::GET_ALL_QUERY);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function findById(int $id) : GuildMember
    {
    }
    
    public function getGuildMemberById(int $guildId, int $memberId) : ?GuildMember
    {
        $stmt = $this->persistence->prepare(self::USER_QUERY);
        $stmt->bindValue('user_id', $memberId, \PDO::PARAM_INT);
        $stmt->bindParam('guild_id', $guildId, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    
}
