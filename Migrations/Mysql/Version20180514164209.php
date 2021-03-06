<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs! This block will be used as the migration description if getDescription() is not used.
 */
class Version20180514164209 extends AbstractMigration
{

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Add updatability for old utf8 databases to Neos 4.0';
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function up(Schema $schema)
    {
        // this up() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');
        /**
         * With the new version of NEOS the recommended database charset is utf8mb4 and not just utf8 anymore.
         * To make the foreign key constraint work the table/cell encoding must match the
         * encoding of typo3_neos_domain_model_site / neos_domain_model_site.
         * Solution: First check encoding of typo3_neos_domain_model_site / neos_domain_model_site,
         * then create neos_googleanalytics_domain_model_siteconfiguration accordingly.
         * AND
         * We need to check what the name of the site table is. If you install Neos, run migrations, then add this package,
         * then run this package's migrations, the name will will be neos_flow_...
         * However, if you install everything in one go and run migrations then, the order will be different because this migration
         * comes before the Flow migration where the table is renamed (Version20161124185047). So we need to check which of these two
         * tables exist and set the FK relation accordingly.
         **/

        if ($this->sm->tablesExist('neos_neos_domain_model_site')) {
            $tableName = 'neos_neos_domain_model_site';
        } elseif ($this->sm->tablesExist('typo3_neos_domain_model_site')) {
            $tableName = 'typo3_neos_domain_model_site';
        } else {
            return; // Nothing to do here
        }

        $columnCharSets = $this->connection->executeQuery("SELECT character_set_name FROM information_schema.`COLUMNS` WHERE table_name = '${tableName}' AND column_name = 'persistence_object_identifier'")->fetch();

        $charSet = $columnCharSets['character_set_name'];

        $this->addSql("CREATE TABLE neos_googleanalytics_domain_model_siteconfiguration (persistence_object_identifier VARCHAR(40) NOT NULL, site VARCHAR(40) DEFAULT NULL, profileid VARCHAR(255) NOT NULL, trackingid VARCHAR(255) NOT NULL, INDEX IDX_D675F674694309E4 (site), PRIMARY KEY(persistence_object_identifier)) DEFAULT CHARACTER SET ${charSet} COLLATE ${charSet}_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE neos_googleanalytics_domain_model_siteconfiguration ADD CONSTRAINT FK_D675F674694309E4 FOREIGN KEY (site) REFERENCES ${tableName} (persistence_object_identifier) ON DELETE CASCADE");
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema)
    {
        // this down() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');

        $this->addSql("DROP TABLE neos_googleanalytics_domain_model_siteconfiguration");
    }
}