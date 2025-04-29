<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250429104459 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE compra (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, cantidad INT NOT NULL, price DOUBLE PRECISION NOT NULL, fecha DATE NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE compra_productos (compra_id INT NOT NULL, productos_id INT NOT NULL, INDEX IDX_EA1E78B6F2E704D7 (compra_id), INDEX IDX_EA1E78B6ED07566B (productos_id), PRIMARY KEY(compra_id, productos_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE productos (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, price INT NOT NULL, image VARCHAR(255) NOT NULL, cantidad INT NOT NULL, is_favorite TINYINT(1) NOT NULL, inside_cart TINYINT(1) NOT NULL, fecha DATE NOT NULL, categoria VARCHAR(100) NOT NULL, subcategoria VARCHAR(100) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reservas (id INT AUTO_INCREMENT NOT NULL, usuario_id INT NOT NULL, servicio VARCHAR(255) NOT NULL, peluquero VARCHAR(255) NOT NULL, dia DATE NOT NULL, hora TIME NOT NULL, precio DOUBLE PRECISION NOT NULL, INDEX IDX_AA1DAB01DB38439E (usuario_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usuario_producto_favorito (id INT AUTO_INCREMENT NOT NULL, usuario_id INT NOT NULL, producto_id INT NOT NULL, is_favorite TINYINT(1) NOT NULL, inside_cart TINYINT(1) NOT NULL, cantidad INT NOT NULL, INDEX IDX_EB86FC90DB38439E (usuario_id), INDEX IDX_EB86FC907645698E (producto_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usuarios (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) DEFAULT NULL, apellidos VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, telefono INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usuarios_productos_favoritos (usuarios_id INT NOT NULL, productos_id INT NOT NULL, INDEX IDX_EE244DB8F01D3B25 (usuarios_id), INDEX IDX_EE244DB8ED07566B (productos_id), PRIMARY KEY(usuarios_id, productos_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE compra_productos ADD CONSTRAINT FK_EA1E78B6F2E704D7 FOREIGN KEY (compra_id) REFERENCES compra (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE compra_productos ADD CONSTRAINT FK_EA1E78B6ED07566B FOREIGN KEY (productos_id) REFERENCES productos (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservas ADD CONSTRAINT FK_AA1DAB01DB38439E FOREIGN KEY (usuario_id) REFERENCES usuarios (id)');
        $this->addSql('ALTER TABLE usuario_producto_favorito ADD CONSTRAINT FK_EB86FC90DB38439E FOREIGN KEY (usuario_id) REFERENCES usuarios (id)');
        $this->addSql('ALTER TABLE usuario_producto_favorito ADD CONSTRAINT FK_EB86FC907645698E FOREIGN KEY (producto_id) REFERENCES productos (id)');
        $this->addSql('ALTER TABLE usuarios_productos_favoritos ADD CONSTRAINT FK_EE244DB8F01D3B25 FOREIGN KEY (usuarios_id) REFERENCES usuarios (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE usuarios_productos_favoritos ADD CONSTRAINT FK_EE244DB8ED07566B FOREIGN KEY (productos_id) REFERENCES productos (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE compra_productos DROP FOREIGN KEY FK_EA1E78B6F2E704D7');
        $this->addSql('ALTER TABLE compra_productos DROP FOREIGN KEY FK_EA1E78B6ED07566B');
        $this->addSql('ALTER TABLE reservas DROP FOREIGN KEY FK_AA1DAB01DB38439E');
        $this->addSql('ALTER TABLE usuario_producto_favorito DROP FOREIGN KEY FK_EB86FC90DB38439E');
        $this->addSql('ALTER TABLE usuario_producto_favorito DROP FOREIGN KEY FK_EB86FC907645698E');
        $this->addSql('ALTER TABLE usuarios_productos_favoritos DROP FOREIGN KEY FK_EE244DB8F01D3B25');
        $this->addSql('ALTER TABLE usuarios_productos_favoritos DROP FOREIGN KEY FK_EE244DB8ED07566B');
        $this->addSql('DROP TABLE compra');
        $this->addSql('DROP TABLE compra_productos');
        $this->addSql('DROP TABLE productos');
        $this->addSql('DROP TABLE reservas');
        $this->addSql('DROP TABLE usuario_producto_favorito');
        $this->addSql('DROP TABLE usuarios');
        $this->addSql('DROP TABLE usuarios_productos_favoritos');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
