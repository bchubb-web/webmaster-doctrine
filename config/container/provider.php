<?php

declare(strict_types=1);

use Doctrine\ORM\Configuration as OrmConfig;
use Doctrine\ORM\EntityManager;
use League\Config\Configuration as SiteConfig;
use League\Container\ServiceProvider\AbstractServiceProvider;

return new class extends AbstractServiceProvider
{
    public function provides(string $id): bool
    {
        return in_array($id, [
        ]);
    }
    public function register(): void
    {
        $this->getContainer->addShared(EntityRegistry::class);

        $this->getContainer
            ->extend(EntityRegistry::class)
            //->addMethodCall('register', [Entity\Thread::class])
            ->addMethodCall('register', [SharedEntity\Author::class])
            ->addMethodCall('register', [Article::class])
        ;

        $this->getContainer
            ->addShared(OrmConfig::class, function (EntityRegistry $registry) {
                $ormConfig = $registry->setup();
                $ormConfig->enableNativeLazyObjects(true);
                return $ormConfig;
            })
            ->addArguments([
                EntityRegistry::class,
            ])
        ;

        $this->getContainer
            ->addShared(
                \Doctrine\DBAL\Connection::class, function (SiteConfig $config, OrmConfig $ormConfig) {
                    $connection = \Doctrine\DBAL\DriverManager::getConnection(
                        $config->get('db.connection', []),
                        $ormConfig,
                    );

                    return $connection;
                })
            ->addArguments([
                SiteConfig::class,
                OrmConfig::class,
            ])
        ;

        $this->getContainer
            ->add(\Doctrine\DBAL\Schema\AbstractSchemaManager::class, function (\Doctrine\DBAL\Connection $connection) {
                return $connection->createSchemaManager();
            })
            ->addArguments([
                \Doctrine\DBAL\Connection::class,
            ])
        ;

        $this->getContainer
            ->addShared(EntityManager::class)
            ->addArguments([
                \Doctrine\DBAL\Connection::class,
                OrmConfig::class,
            ])
        ;
    }
};

