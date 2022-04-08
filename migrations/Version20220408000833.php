<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220408000833 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE answer_vote_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE answer_vote (id INT NOT NULL, author_id INT NOT NULL, answer_id INT NOT NULL, value INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_43B66A4F675F31B ON answer_vote (author_id)');
        $this->addSql('CREATE INDEX IDX_43B66A4AA334807 ON answer_vote (answer_id)');
        $this->addSql('CREATE UNIQUE INDEX author_answer_unique_idx ON answer_vote (author_id, answer_id)');
        $this->addSql('ALTER TABLE answer_vote ADD CONSTRAINT FK_43B66A4F675F31B FOREIGN KEY (author_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE answer_vote ADD CONSTRAINT FK_43B66A4AA334807 FOREIGN KEY (answer_id) REFERENCES answer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE answer ADD vote_count INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE answer_vote_id_seq CASCADE');
        $this->addSql('DROP TABLE answer_vote');
        $this->addSql('ALTER TABLE answer DROP vote_count');
    }
}
