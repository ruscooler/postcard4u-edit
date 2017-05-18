<?php

use Phinx\Migration\AbstractMigration;

class TicketsTable extends AbstractMigration
{
    public function up()
    {
        $users_table = $this->table('users');
        $users_table->addColumn('login', 'string')
            ->addColumn('password', 'string')
            ->create();

        $users_table->insert([
            ["login" => "admin","password" => md5("redirect735018") ],
        ]);
        $users_table->saveData();

        $tickets_table = $this->table('tickets');
        $tickets_table->addColumn('title', 'string')
            ->addColumn('linkfrom', 'text')
            ->addColumn('linkto', 'text')
            ->create();

    }

    public function down() {
        $this->dropTable('tickets');
        $this->dropTable('users');
    }
}
