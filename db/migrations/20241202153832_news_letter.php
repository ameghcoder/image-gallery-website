<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class NewsLetter extends AbstractMigration
{
    public function up(): void
    {   
        if(!$this->hasTable('newsletter')){
            // create newsletter table
            $table = $this->table('newsletter');

            $table->addColumn('email', 'string', ['limit' => 250, 'null' => false])
            ->addIndex(['email'], ['unique' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->create();

        }
    }

    public function down(){
        $this->table('newsletter')->drop()->save();
    }
}
