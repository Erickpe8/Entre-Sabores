<?php

namespace Tests\Feature;

use PDO;
use Tests\TestCase;

class MysqlConnectTimeoutConfigTest extends TestCase
{
    public function test_mysql_and_mariadb_define_a_short_connect_timeout(): void
    {
        if (! extension_loaded('pdo_mysql')) {
            $this->markTestSkipped('pdo_mysql no está disponible en este entorno.');
        }

        foreach (['mysql', 'mariadb'] as $connection) {
            $options = config("database.connections.{$connection}.options");

            $this->assertIsArray($options);
            $this->assertArrayHasKey(PDO::ATTR_TIMEOUT, $options);
            $this->assertSame(5, $options[PDO::ATTR_TIMEOUT]);

            if (defined('PDO::MYSQL_ATTR_CONNECT_TIMEOUT')) {
                $this->assertArrayHasKey(PDO::MYSQL_ATTR_CONNECT_TIMEOUT, $options);
                $this->assertSame(5, $options[PDO::MYSQL_ATTR_CONNECT_TIMEOUT]);
            }
        }
    }
}
