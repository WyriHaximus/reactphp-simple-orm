<?php declare(strict_types=1);
use Phinx\Seed\AbstractSeed;

class UsersSeed extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            [
                'id' => 1,
                'name' => 'Deathwing',
            ],
            [
                'id' => 2,
                'name' => 'Gandalf',
            ],
            [
                'id' => 3,
                'name' => 'Floki',
            ],
        ];

        $table = $this->table('users');
        $table->insert($data)->save();
    }
}
