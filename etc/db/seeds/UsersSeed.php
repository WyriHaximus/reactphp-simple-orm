<?php declare(strict_types=1);
use Phinx\Seed\AbstractSeed;

class UsersSeed extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            [
                'id' => 'fb175cbc-04cc-41c7-8e35-6b817ac016ca',
                'name' => 'Deathwing',
            ],
            [
                'id' => '15f25357-4b3d-4d4d-b6a5-2ceb93864b77',
                'name' => 'Gandalf',
            ],
            [
                'id' => '2fa0d077-d374-4409-b1ef-9687c6729158',
                'name' => 'Floki',
            ],
        ];

        $table = $this->table('users');
        $table->insert($data)->save();
    }
}
