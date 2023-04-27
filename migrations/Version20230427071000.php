<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230427071000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'create User and Token tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            CREATE TABLE security_users (
                id varchar(36) PRIMARY KEY NOT NULL, 
                email varchar(255) NOT NULL, 
                password varchar(255) NOT NULL,
                created_at datetime NOT NULL,
                updated_at datetime NOT NULL,
                is_active bool DEFAULT false
           );
       ");
        $this->addSql("
            CREATE TABLE security_tokens (
                value varchar(255) NOT NULL, 
                created_at datetime NOT NULL,
                expired_at datetime NOT NULL,
                type varchar(255) NOT NULL,
                user_id varchar(36) NOT NULL,
                FOREIGN KEY (user_id)
                    REFERENCES security_users(id)
                    ON DELETE CASCADE 
           );
       ");
        $this->addSql("CREATE UNIQUE INDEX idx_security_users_email ON security_users (email);");
        $this->addSql("CREATE INDEX idx_security_tokens_user_id ON security_tokens (user_id);");
        $this->addSql("CREATE INDEX idx_security_tokens_value ON security_tokens (value);");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DROP INDEX idx_security_users_email;");
        $this->addSql("DROP INDEX idx_security_tokens_user_id;");
        $this->addSql("DROP TABLE security_users;");
        $this->addSql("DROP TABLE security_tokens;");
    }
}
