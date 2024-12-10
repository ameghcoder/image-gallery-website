<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UserPanel extends AbstractMigration
{
    public function up() : void {
        if(!$this->hasTable('users')){
            $table = $this->table('users');

            $table
            ->addColumn('name', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('email', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('password', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('country', 'string', ['limit' => 6, 'null' => false])
            ->addColumn('is_verified', 'boolean', ['default' => false])
            ->addColumn('joined_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('verify_code', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('verify_code_expiry', 'string', ['null' => true])
            ->addIndex(['email'], ['unique' => true])
            ->create();
        }
    }

    public function down(){
        $this->table('users')->drop()->save();
    }
}
