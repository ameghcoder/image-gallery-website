<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AdminPanel extends AbstractMigration
{
    public function up(){
        // check the table exists or not
        if(!$this->hasTable('adminpanel')){
            // create adminpanel table
            $table = $this->table('adminpanel');

            $table->addColumn('email', 'string', ['limit' => 250, 'null' => false])
            ->addIndex(['email'], ['unique' => true])
            ->addColumn('type', 'string', ['limit' => 20, 'null' => false])
            ->addColumn('permissions', 'string', ['limit' => 20, 'null' => false])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false, 'update' => 'CURRENT_TIMESTAMP'])
            ->create();
        }
    }

    public function down(){

    }
}
