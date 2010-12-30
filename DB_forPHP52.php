<?php
    class DB
    {
        protected static $instance = null;

        final private function __construct() {}
        final private function __clone() {}

        /**
         * @return PDO
         */
        public static function instance() {
            if (self::$instance === null) {
                try {
                    self::$instance = new PDO(
                        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
                        DB_USER,
                        DB_PASS,
                        array(
                            PDO::ATTR_PERSISTENT => true,
                            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET CHARACTER SET utf8',
                        )
                    );
                    self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                }
                catch (PDOException $e) {
                    die('Database connection could not be established.');
                }
            }

            return self::$instance;
        }

        /**
         * @return PDOStatement
         */
        public static function q($query) {
            if (func_num_args() == 1) {
                return self::instance()->query($query);
            }

            $args = func_get_args();
            return self::instance()->query(self::autoQuote(array_shift($args), $args));
        }

        public static function x($query) {
            if (func_num_args() == 1) {
                return self::instance()->exec($query);
            }

            $args = func_get_args();
            return self::instance()->exec(self::autoQuote(array_shift($args), $args));
        }

        public static function autoQuote($query, array $args) {
            $i = strlen($query);
            $c = count($args);

            if ($c != substr_count($query, '?')) {
                throw new UnexpectedValueException('Wrong parameter count: Number of placeholders and parameters does not match');
            }

            while ($c--) {
                while ($i-- && $query[$i] != '?');

                // $i+1 is the quote-r
                if (!isset($query[$i+1]) || false === $type = strpos('sia', $query[$i+1])) {
                    // no or unsupported quote-r given
                    // => direct insert
                    $query = substr_replace($query, $args[$c], $i, 1);
                    continue;
                }

                if ($type == 0) {
                    $replace = self::instance()->quote($args[$c]);
                } elseif ($type == 1) {
                    $replace = intval($args[$c]);
                } elseif ($type == 2) {
                    foreach ($args[$c] as &$value) {
                        $value = self::instance()->quote($value);
                    }
                    $replace = '(' . implode(',', $args[$c]) . ')';
                }

                $query = substr_replace($query, $replace, $i, 2);
            }

            return $query;
        }

        public static function beginTransaction() {
            return self::instance()->beginTransaction();
        }
        public static function commit() {
            return self::instance()->commit();
        }
        public static function errorCode() {
            return self::instance()->errorCode();
        }
        public static function errorInfo() {
            return self::instance()->errorInfo();
        }
        public static function exec($statement) {
            return self::instance()->exec($statement);
        }
        public static function getAttribute($attribute) {
            return self::instance()->getAttribute($attribute);
        }
        public static function getAvailableDrivers() {
            return self::instance()->getAvailableDrivers();
        }
        public static function inTransaction() {
            return self::instance()->inTransaction();
        }
        public static function lastInsertId($name = NULL) {
            return self::instance()->lastInsertId($name);
        }
        public static function prepare($statement, $driver_options = array()) {
            return self::instance()->prepare($statement, $driver_options);
        }
        public static function query() {
            $arguments = func_get_args();
            return call_user_func_array(array(self::instance(), 'query'), $arguments);
        }
        public static function quote($string, $parameter_type = PDO::PARAM_STR) {
            return self::instance()->quote($string, $parameter_type);
        }
        public static function rollBack() {
            return self::instance()->rollBack();
        }
        public static function setAttribute($attribute, $value) {
            return self::instance()->setAttribute($attribute, $value);
        }
    }