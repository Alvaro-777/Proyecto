<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260112150327 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE archivo (id INT AUTO_INCREMENT NOT NULL, usuario_id INT NOT NULL, nombre VARCHAR(100) NOT NULL, peso INT DEFAULT NULL, tipo VARCHAR(50) DEFAULT NULL, fecha_subida DATETIME NOT NULL, INDEX IDX_3529B482DB38439E (usuario_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE historial_uso_ia (id INT AUTO_INCREMENT NOT NULL, usuario_id INT NOT NULL, ia_id INT NOT NULL, archivo_id INT DEFAULT NULL, texto_input VARCHAR(255) DEFAULT NULL, fecha DATETIME NOT NULL, ip_anonimo VARCHAR(45) DEFAULT NULL, INDEX IDX_D9EF0CB4DB38439E (usuario_id), INDEX IDX_D9EF0CB4489A6E65 (ia_id), UNIQUE INDEX UNIQ_D9EF0CB446EBF93B (archivo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ia (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(100) NOT NULL, costo_creditos INT NOT NULL, accesible_anonimos TINYINT(1) NOT NULL, url_externa VARCHAR(255) DEFAULT NULL, entrada_permitida VARCHAR(255) NOT NULL, tipo VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pago (id INT AUTO_INCREMENT NOT NULL, usuario_id INT NOT NULL, cantidad NUMERIC(10, 2) NOT NULL, creditos_obtenidos INT NOT NULL, fecha DATETIME NOT NULL, metodo VARCHAR(50) DEFAULT NULL, valido TINYINT(1) DEFAULT 1 NOT NULL, INDEX IDX_F4DF5F3EDB38439E (usuario_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usuario (id INT AUTO_INCREMENT NOT NULL, correo VARCHAR(100) NOT NULL, pswd VARCHAR(100) NOT NULL, nombre VARCHAR(50) NOT NULL, apellido VARCHAR(50) NOT NULL, creditos INT DEFAULT 50, fecha_registro DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE archivo ADD CONSTRAINT FK_3529B482DB38439E FOREIGN KEY (usuario_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE historial_uso_ia ADD CONSTRAINT FK_D9EF0CB4DB38439E FOREIGN KEY (usuario_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE historial_uso_ia ADD CONSTRAINT FK_D9EF0CB4489A6E65 FOREIGN KEY (ia_id) REFERENCES ia (id)');
        $this->addSql('ALTER TABLE historial_uso_ia ADD CONSTRAINT FK_D9EF0CB446EBF93B FOREIGN KEY (archivo_id) REFERENCES archivo (id)');
        $this->addSql('ALTER TABLE pago ADD CONSTRAINT FK_F4DF5F3EDB38439E FOREIGN KEY (usuario_id) REFERENCES usuario (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE archivo DROP FOREIGN KEY FK_3529B482DB38439E');
        $this->addSql('ALTER TABLE historial_uso_ia DROP FOREIGN KEY FK_D9EF0CB4DB38439E');
        $this->addSql('ALTER TABLE historial_uso_ia DROP FOREIGN KEY FK_D9EF0CB4489A6E65');
        $this->addSql('ALTER TABLE historial_uso_ia DROP FOREIGN KEY FK_D9EF0CB446EBF93B');
        $this->addSql('ALTER TABLE pago DROP FOREIGN KEY FK_F4DF5F3EDB38439E');
        $this->addSql('DROP TABLE archivo');
        $this->addSql('DROP TABLE historial_uso_ia');
        $this->addSql('DROP TABLE ia');
        $this->addSql('DROP TABLE pago');
        $this->addSql('DROP TABLE usuario');
    }
}
