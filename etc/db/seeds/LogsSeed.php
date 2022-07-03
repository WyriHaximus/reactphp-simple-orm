<?php declare(strict_types=1);
use Phinx\Seed\AbstractSeed;
use Ramsey\Uuid\Uuid;

class LogsSeed extends AbstractSeed
{
    public function run(): void
    {
        $data = [];
        for ($i = 0; $i < 256; $i++) {
            $data[] = [
                'id' => Uuid::getFactory()->uuid4(),
                'message' => 'Message #' . $i,
                'created' => (new DateTimeImmutable())->format('Y-m-d H:i:s e'),
                'modified' => (new DateTimeImmutable())->format('Y-m-d H:i:s e'),
            ];
        }

        $table = $this->table('logs');
        $table->insert($data)->save();
    }
}
