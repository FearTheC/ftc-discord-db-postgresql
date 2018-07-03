<?php declare(strict_types=1);

namespace FTC\Discord\Db\Postgresql;

use FTC\Discord\Model\UserRepository as RepositoryInterface;
use FTC\Discord\Model\User;
use FTC\Discord\Model\ValueObject\Snowflake;

class UserRepository extends PostgresqlRepository implements RepositoryInterface
{
    
    const SELECT_USER = <<<'EOT'
SELECT * from users
WHERE id = :id;
EOT;
    const INSERT_USER = "INSERT INTO users VALUES (:id, :username, :tag, :email, :is_bot) ON CONFLICT (id) DO UPDATE SET username = :username, email = :email";
    
    /**
     * @var User[]
     */
    private $users;
    
    public function save(User $user)
    {
        $this->saveUser($user);
        
//         $this->persistence->beginTransaction();
//         $this->saveGuild($guild);
//         array_map(
//             [$this, 'saveMember'],
//             $guild->getMembers()->toArray(),
//             array_fill(0, $guild->getMembers()->count(), $guild->getId())
//             );
//         array_map(
//             [$this, 'saveRole'],
//             $guild->getRoles()->toArray(),
//             array_fill(0, $guild->getRoles()->count(), $guild->getId())
//             );
//         $this->persistence->commit();
    }
    
    public function getAll() : array
    {
        
    }
    
    public function findById(Snowflake $id) : ?User
    {
        $stmt = $this->persistence->prepare(self::SELECT_USER);
        $stmt->bindValue('id', $id->get(), \PDO::PARAM_INT);
        $stmt->execute();
        
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        $userId = Snowflake::create($data['id']);
        $userTag = DiscordTag::create($data['tag']);
        $userEmail = Email::create($data['email']);
        $user = User::create($userId, $data['username'], $userTag, $userEmail);
        
        return $user;
        
        
//         $stmt = $this->persistence->prepare(self::SELECT_GUILD);
//         $stmt->bindValue('id', $id->get(), \PDO::PARAM_INT);
//         $stmt->execute();
//         $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        
//         $guildId = Snowflake::create($row['id']);
        
        
//         $members = new GuildMemberCollection();
//         $roles = new GuildRoleCollection();
        
//         foreach (json_decode($row['roles'], true) as $role) {
//             $roles->add(GuildRole::create(Snowflake::create($role['id']), $role['name']));
//         }
        
//         foreach (json_decode($row['members'], true) as $member) {
//             $members->add(GuildMember::create($guildId, User::create(Snowflake::create($member['user_id']), 'nickname'), $roles));
//         }
        
//         $guild = Guild::create(
//             $guildId,
//             $row['name'],
//             Snowflake::create(272341331328761888),
//             $roles,
//             $members);
        
//         return $guild;
    }
    
    private function saveUser(User $user) : void
    {
        $stmt = $this->persistence->prepare(self::INSERT_USER);
        $stmt->bindValue('id', $user->getId()->get(), \PDO::PARAM_INT);
        $stmt->bindValue('username', $user->getUsername(), \PDO::PARAM_STR);
        $stmt->bindValue('tag', $user->getTag(), \PDO::PARAM_STR);
        $stmt->bindValue('email', $user->getEmail(), \PDO::PARAM_STR);
        $stmt->bindValue('is_bot', $user->isBot(), \PDO::PARAM_BOOL);
        $stmt->execute();
    }
    
}
