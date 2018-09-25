<?php

declare(strict_types=1);

namespace FTC\Discord\Db\Postgresql;

use FTC\Discord\Model\Aggregate\UserRepository as RepositoryInterface;
use FTC\Discord\Model\Aggregate\User;
use FTC\Discord\Model\ValueObject\Snowflake;
use FTC\Discord\Model\ValueObject\Snowflake\UserId;

class UserRepository extends PostgresqlRepository implements RepositoryInterface
{
    
    const SELECT_USER = <<<'EOT'
SELECT * from users
WHERE id = :id;
EOT;
    const INSERT_USER = <<<'EOT'
INSERT INTO users VALUES (:id, :username, :tag, :email, :is_bot)
ON CONFLICT (id) DO UPDATE SET username = :username, email = :email;
EOT;
    
    /**
     * @var User[]
     */
    private $users;
    
    public function save(User $user)
    {
        $stmt = $this->persistence->prepare(self::INSERT_USER);
        $stmt->bindValue('id', $user->getId()->get(), \PDO::PARAM_INT);
        $stmt->bindValue('username', $user->getUsername(), \PDO::PARAM_STR);
        $stmt->bindValue('tag', $user->getTag(), \PDO::PARAM_STR);
        $stmt->bindValue('email', $user->getEmail(), \PDO::PARAM_STR);
        $stmt->bindValue('is_bot', $user->isBot(), \PDO::PARAM_BOOL);
        $stmt->execute();
    }
    
    public function getAll() : array
    {
        
    }
    
    public function findById(UserId $id) : ?User
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
    }
    
}
