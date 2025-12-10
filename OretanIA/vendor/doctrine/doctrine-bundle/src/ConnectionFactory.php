<?php

declare(strict_types=1);

namespace Doctrine\Bundle\DoctrineBundle;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Connection\StaticServerVersionProvider;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\DBAL\Exception\DriverRequired;
use Doctrine\DBAL\Exception\InvalidWrapperClass;
use Doctrine\DBAL\Exception\MalformedDsnException;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\DBAL\Types\Type;

use function array_merge;
use function is_subclass_of;

use const PHP_EOL;

/**
 * @internal This class is not meant to be used outside this bundle
 *
 * @phpstan-import-type Params from DriverManager
 */
final class ConnectionFactory
{
    /** @internal */
    public const array DEFAULT_SCHEME_MAP = [
        'db2'        => 'ibm_db2',
        'mssql'      => 'pdo_sqlsrv',
        'mysql'      => 'pdo_mysql',
        'mysql2'     => 'pdo_mysql', // Amazon RDS, for some weird reason
        'postgres'   => 'pdo_pgsql',
        'postgresql' => 'pdo_pgsql',
        'pgsql'      => 'pdo_pgsql',
        'sqlite'     => 'pdo_sqlite',
        'sqlite3'    => 'pdo_sqlite',
    ];

    /** @phpstan-ignore property.onlyWritten */
    private readonly DsnParser $dsnParser;

    private bool $initialized = false;

    /** @param mixed[][] $typesConfig */
    public function __construct(
        private readonly array $typesConfig = [],
        DsnParser|null $dsnParser = null,
    ) {
        $this->dsnParser = $dsnParser ?? new DsnParser(self::DEFAULT_SCHEME_MAP);
    }

    /**
     * Create a connection by name.
     *
     * @param mixed[]               $params
     * @param array<string, string> $mappingTypes
     * @phpstan-param Params $params
     */
    public function createConnection(
        array $params,
        Configuration|null $config = null,
        array $mappingTypes = [],
    ): Connection {
        if (! $this->initialized) {
            $this->initializeTypes();
        }

        $params = $this->parseDatabaseUrl($params);

        // URL support for PrimaryReplicaConnection
        if (isset($params['primary'])) {
            $params['primary'] = $this->parseDatabaseUrl($params['primary']);
        }

        if (isset($params['replica'])) {
            foreach ($params['replica'] as $key => $replicaParams) {
                $params['replica'][$key] = $this->parseDatabaseUrl($replicaParams);
            }
        }

        /** @phpstan-ignore-next-line We should adjust when https://github.com/phpstan/phpstan/issues/12414 is fixed */
        if (! isset($params['pdo']) && (! isset($params['charset']) || isset($params['dbname_suffix']))) {
            $wrapperClass = null;

            if (isset($params['wrapperClass'])) {
                if (! is_subclass_of($params['wrapperClass'], Connection::class)) {
                    throw InvalidWrapperClass::new($params['wrapperClass']);
                }

                $wrapperClass           = $params['wrapperClass'];
                $params['wrapperClass'] = null;
            }

            $connection = DriverManager::getConnection($params, $config);
            $params     = $this->addDatabaseSuffix($connection->getParams());
            $driver     = $connection->getDriver();
            $platform   = $driver->getDatabasePlatform(new StaticServerVersionProvider(
                $params['serverVersion'] ?? $params['primary']['serverVersion'] ?? '',
            ));

            if (! isset($params['charset'])) {
                if ($platform instanceof AbstractMySQLPlatform) {
                    $params['charset'] = 'utf8mb4';
                    if (! isset($params['defaultTableOptions']['collation'])) {
                        $params['defaultTableOptions']['collation'] = 'utf8mb4_unicode_ci';
                    }
                } else {
                    $params['charset'] = 'utf8';
                }
            }

            if ($wrapperClass !== null) {
                $params['wrapperClass'] = $wrapperClass;
            } else {
                $wrapperClass = Connection::class;
            }

            $connection = new $wrapperClass($params, $driver, $config);
        } else {
            $connection = DriverManager::getConnection($params, $config);
        }

        if (! empty($mappingTypes)) {
            $platform = $this->getDatabasePlatform($connection);
            foreach ($mappingTypes as $dbType => $doctrineType) {
                $platform->registerDoctrineTypeMapping($dbType, $doctrineType);
            }
        }

        return $connection;
    }

    /**
     * Try to get the database platform.
     *
     * This could fail if types should be registered to an predefined/unused connection
     * and the platform version is unknown.
     *
     * @link https://github.com/doctrine/DoctrineBundle/issues/673
     *
     * @throws DBALException
     */
    private function getDatabasePlatform(Connection $connection): AbstractPlatform
    {
        try {
            return $connection->getDatabasePlatform();
        } catch (DriverException $driverException) {
            throw new ConnectionException(
                'An exception occurred while establishing a connection to figure out your platform version.' . PHP_EOL .
                "You can circumvent this by setting a 'server_version' configuration value" . PHP_EOL . PHP_EOL .
                'For further information have a look at:' . PHP_EOL .
                'https://github.com/doctrine/DoctrineBundle/issues/673',
                0,
                $driverException,
            );
        }
    }

    /**
     * initialize the types
     */
    private function initializeTypes(): void
    {
        foreach ($this->typesConfig as $typeName => $typeConfig) {
            if (Type::hasType($typeName)) {
                Type::overrideType($typeName, $typeConfig['class']);
            } else {
                Type::addType($typeName, $typeConfig['class']);
            }
        }

        $this->initialized = true;
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    private function addDatabaseSuffix(array $params): array
    {
        if (isset($params['dbname']) && isset($params['dbname_suffix'])) {
            $params['dbname'] .= $params['dbname_suffix'];
        }

        foreach ($params['replica'] ?? [] as $key => $replicaParams) {
            if (! isset($replicaParams['dbname'], $replicaParams['dbname_suffix'])) {
                continue;
            }

            $params['replica'][$key]['dbname'] .= $replicaParams['dbname_suffix'];
        }

        if (isset($params['primary']['dbname'], $params['primary']['dbname_suffix'])) {
            $params['primary']['dbname'] .= $params['primary']['dbname_suffix'];
        }

        return $params;
    }

    /**
     * Extracts parts from a database URL, if present, and returns an
     * updated list of parameters.
     *
     * @param mixed[] $params The list of parameters.
     * @phpstan-param Params $params
     *
     * @return mixed[] A modified list of parameters with info from a database
     *                 URL extracted into individual parameter parts.
     * @phpstan-return Params
     *
     * @throws DBALException
     *
     * @phpstan-ignore throws.unusedType
     */
    private function parseDatabaseUrl(array $params): array
    {
        /** @phpstan-ignore isset.offset (for DBAL < 4) */
        if (! isset($params['url'])) {
            return $params;
        }

        /** @phpstan-ignore deadCode.unreachable */
        try {
            $parsedParams = $this->dsnParser->parse($params['url']);
        } catch (MalformedDsnException $e) {
            throw new MalformedDsnException('Malformed parameter "url".', 0, $e);
        }

        if (isset($parsedParams['driver'])) {
            // The requested driver from the URL scheme takes precedence
            // over the default custom driver from the connection parameters (if any).
            unset($params['driverClass']);
        }

        $params = array_merge($params, $parsedParams);

        // If a schemeless connection URL is given, we require a default driver or default custom driver
        // as connection parameter.
        if (! isset($params['driverClass']) && ! isset($params['driver'])) {
            throw DriverRequired::new($params['url']);
        }

        unset($params['url']);

        return $params;
    }
}
