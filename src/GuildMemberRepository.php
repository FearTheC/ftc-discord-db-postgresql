<?php
namespace FTC\Discord\Db\Postgresql;

use FTC\Discord\Db\Postgresql\GuildMemberRepository as RepositoryInterface;
use FTC\Discord\Model\GuildMember;

class GuildMemberRepository extends PostgresqlRepository implements RepositoryInterface
{
    
    const GET_ALL_QUERY = 'SELECT id, username FROM users ORDER BY id DESC';
    
    const ADD_USER_Q = "INSERT INTO users VALUES (:user_id, :username) ON CONFLICT ON CONSTRAINT users_pkey DO NOTHING";
    
    const ADD_GUILD_USER_Q = "INSERT INTO guilds_users VALUES (:guild_id, :user_id)";
    
    const ADD_USER_ROLE = "INSERT INTO users_roles VALUES (:user_id, role)";
    
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

    
}
