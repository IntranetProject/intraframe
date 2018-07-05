<?php
/**
 * Created by PhpStorm.
 * User: bennet
 * Date: 06.07.18
 * Time: 00:39
 */

namespace intraframe\Util;


class Config {

    /*
     * things you are allowed to edit
     */

    public function getSQLHost() {
        return MYSQL_HOST;
    }

    public function getSQLUsername() {
        return MYSQL_USER;
    }

    public function getSQLPassword() {
        return MYSQL_PASSWORD;
    }

    public function getSQLDatabase() {
        return MYSQL_DATABASE;
    }

    protected static $_instance = null;

    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    protected function __clone() {
    }

    protected function __construct() {
    }
}