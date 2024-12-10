<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ContactUs extends AbstractMigration
{
    public function up(): void
    {   
        // check the table exists or not
        if(!$this->hasTable('contactus')){
            // create contact us table
            $table = $this->table('contactus');

            $table->addColumn('name', 'string', ['limit' => 25, 'null' => false])
            ->addColumn('email', 'string', ['limit' => 250, 'null' => false])
            ->addColumn('country', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('subject', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('message', 'text', ['null' => false])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->create();
        }
    }

    public function down(){
        $this->table('contactus')->drop()->save();
    }
}
