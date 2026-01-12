<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20269111110807 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
        INSERT INTO ia (nombre, tipo, costo_creditos, entrada_permitida, accesible_anonimos, url_externa)
        VALUES
        ('Audio-IA', 'texto_audio', 0, 'ambas', 1, '/php/audio.php'),
        ('Predict-IA', 'predictiva', 1, 'documento', 0, '/php/predictiva.php'),
        ('Chatbot-IA', 'chatbot', 1, 'texto', 0, '/php/chatbot.php')
    ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DELETE FROM IA");
    }
}
