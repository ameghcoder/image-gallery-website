<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Wallpapers extends AbstractMigration
{
    public function up(){
        // check the table exists or not
        if(!$this->hasTable('wallpapers')){
            // create wallpapers table without the defautl 'id' column
            $table = $this->table('wallpapers', ['id' => false, 'primary_key' => ['id']]); // Disable Phinx's default 'id' column

            // Add custom 'id' column (primary key) and other columns
            $table->addColumn('id', 'integer', ['identity' => true])
            ->addColumn('title', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('description', 'string', ['limit' => 300, 'null' => false])
            ->addColumn('filename', 'string', ['limit' => 250, 'null' => false])
            ->addIndex(['filename'], ['unique' => true])
            ->addColumn('views', 'integer', ['null' => true])
            ->addColumn('downloads', 'integer', ['null' => true])
            ->addColumn('shares', 'integer', ['null' => true])
            ->addColumn('size', 'string', ['limit' => 15, 'null' => true])
            ->addColumn('device', 'string', ['limit' => 6, 'null' => true])
            ->addColumn('resolution', 'string', ['limit' => 15, 'null' => true])
            ->addColumn('userId', 'integer', ['null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false, 'update' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('userId', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])
            ->create();

            $this->execute('ALTER TABLE wallpapers ADD FULLTEXT ft_title_description (title, description)');
        }
   }

   public function down() {
        $this->execute('ALTER TABLE wallpapers DROP INDEX ft_title_description');  
    
        $this->table('wallpapers')->drop()->save();
   }
}
