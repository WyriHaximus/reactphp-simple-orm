<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Composer;

use EventSauce\ObjectHydrator\ObjectMapperCodeGenerator;
use LogicException;
use WyriHaximus\Composer\GenerativePluginTooling\Filter\Class\ImplementsInterface;
use WyriHaximus\Composer\GenerativePluginTooling\Filter\Class\IsInstantiable;
use WyriHaximus\Composer\GenerativePluginTooling\Filter\Package\ComposerJsonHasItemWithSpecificValue;
use WyriHaximus\Composer\GenerativePluginTooling\GenerativePlugin;
use WyriHaximus\Composer\GenerativePluginTooling\Helper\File;
use WyriHaximus\Composer\GenerativePluginTooling\Helper\Remove;
use WyriHaximus\Composer\GenerativePluginTooling\Helper\TwigFile;
use WyriHaximus\Composer\GenerativePluginTooling\Item as ItemContract;
use WyriHaximus\Composer\GenerativePluginTooling\LogStages;
use WyriHaximus\React\SimpleORM\Configuration;
use WyriHaximus\React\SimpleORM\EntityInspector;
use WyriHaximus\React\SimpleORM\EntityInterface;
use WyriHaximus\React\SimpleORM\Generated\Hydrator;

use function array_map;
use function md5;

use const PHP_EOL;

final class Plugin implements GenerativePlugin
{
    public static function name(): string
    {
        return 'wyrihaximus/react-simple-orm';
    }

    public static function log(LogStages $stage): string
    {
        return match ($stage) {
            LogStages::Init => 'Locating entities',
            LogStages::Error => 'An error occurred: %s',
            LogStages::Collected => 'Found %d action(s)',
            LogStages::Completion => 'Generated static abstract action manager and action list in %s second(s)',
        };
    }

    /** @inheritDoc */
    public function filters(): iterable
    {
        yield new ComposerJsonHasItemWithSpecificValue('wyrihaximus.react.orm.has-entities', true);
        yield new IsInstantiable();
        yield new ImplementsInterface(EntityInterface::class);
    }

    /** @inheritDoc */
    public function collectors(): iterable
    {
        yield new Collector();
    }

    public function compile(string $rootPath, ItemContract ...$items): void
    {
        Remove::directoryContents($rootPath . '/src/Generated');

        File::write(
            $rootPath . '/src/Generated/Hydrator.php',
            new ObjectMapperCodeGenerator()->dump(
                array_map(
                    $this->entityClass(...),
                    $items,
                ),
                Hydrator::class,
            ) . PHP_EOL,
        );

        $entityToGenerateClassesClassNameSuffixMapping = [];
        foreach ($items as $item) {
            $entityClass                                                 = $this->entityClass($item);
            $entityToGenerateClassesClassNameSuffixMapping[$entityClass] = 'IE' . md5($entityClass);
        }

        TwigFile::render(
            $rootPath . '/etc/generated_templates/InspectedEntityMap.php.twig',
            $rootPath . '/src/Generated/InspectedEntityMap.php',
            ['map' => $entityToGenerateClassesClassNameSuffixMapping],
        );

        $entityInspector = new EntityInspector(new Configuration());
        foreach ($items as $item) {
            $entityClass = $this->entityClass($item);
            TwigFile::render(
                $rootPath . '/etc/generated_templates/InspectedEntity.php.twig',
                $rootPath . '/src/Generated/InspectedEntity/' . $entityToGenerateClassesClassNameSuffixMapping[$entityClass] . '.php',
                [
                    'entity' => $entityInspector->entity($entityClass),
                    'entityToGenerateClassesClassNameSuffixMapping' => $entityToGenerateClassesClassNameSuffixMapping,
                ],
            );
        }
    }

    /** @return class-string<EntityInterface> */
    private function entityClass(ItemContract $item): string
    {
        if (! $item instanceof Item) {
            throw new LogicException('Expected an entity item collected by ' . Collector::class);
        }

        return $item->class;
    }
}
