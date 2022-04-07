<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220407232407 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE question_vote_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE question_vote (id INT NOT NULL, author_id INT NOT NULL, question_id INT NOT NULL, value INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4FE688BF675F31B ON question_vote (author_id)');
        $this->addSql('CREATE INDEX IDX_4FE688B1E27F6BF ON question_vote (question_id)');
        $this->addSql('CREATE UNIQUE INDEX author_question_unique_idx ON question_vote (author_id, question_id)');
        $this->addSql('ALTER TABLE question_vote ADD CONSTRAINT FK_4FE688BF675F31B FOREIGN KEY (author_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE question_vote ADD CONSTRAINT FK_4FE688B1E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE question_vote_id_seq CASCADE');
        $this->addSql('DROP TABLE question_vote');
    }
}
