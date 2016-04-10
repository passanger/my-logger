<?php

    require_once('BaseLogger.php');
    require_once('BaseLoggerInterface.php');
    require_once('LogLevel.php');
    /**
     * Concrete implementation for the MyLogger
     */
    class MyLogger extends BaseLogger{

        /**
         * Octal default permissions of the log file
         * @var integer
         */
        private $defaultPermissions = 0777;

        /**
         * file handle for the log file
         * @var
         */
        private $fileHandle;

        /**
         * connection to database
         * @var
         */
        private static $connection;

        /**
         * Log file path
         * @var string
         */
        private $logFilePath;

        /**
         * Last line logged to the logger
         * @var string
         */
        private $lastLine = '';

        /**
         * The number of lines logged
         * @var int
         */
        private $logLineCount = 0;

        /**
         * @var StorageType
         */
        private $storageType;

        /**
         * Default constructor
         */
        public function __construct(){
            $a = func_get_args();
            $i = func_num_args();
            if (method_exists($this,$f='__construct'.$i)) {
                call_user_func_array(array($this,$f),$a);
            }
        }


        /**
         * Constructor for the StorageType::FILE_SYSTEM
         * Constructor name __construct.NUMBER_OF_PARAMETERS
         *
         * @param $logDirectory
         * @param string $storageType
         * @throws RuntimeException
         */
        public function __construct2($logDirectory, $storageType = StorageType::FILE_SYSTEM){
            $this->setStorageType($storageType);
            $logDirectory = rtrim($logDirectory, DIRECTORY_SEPARATOR);
            if ( ! file_exists($logDirectory)) {
                mkdir($logDirectory, $this->defaultPermissions, true);
            }
            if(strpos($logDirectory, 'php://') === 0) {
                $this->setLogToStdOut($logDirectory);
                $this->setFileHandle('w+');
            } else {
                $this->setLogFilePath($logDirectory);
                if(file_exists($this->logFilePath) && !is_writable($this->logFilePath)) {
                    throw new RuntimeException('The file could not be saved');
                }
                $this->setFileHandle('a');
            }
            if ( ! $this->fileHandle) {
                throw new RuntimeException('The file could not be opened. Check permissions.');
            }
        }

        /**
         * Constructor for the StorageType::DATABASE
         * Constructor name __construct.NUMBER_OF_PARAMETERS
         *
         * @param $databaseHost
         * @param $databaseName
         * @param $user
         * @param $password
         * @param string $storageType
         * @throws RuntimeException
         */
        public function __construct5($databaseHost, $databaseName, $user, $password, $storageType = StorageType::DATABASE) {
            $this->setStorageType($storageType);
            if(!isset(self::$connection)){
                $host = 'mysql:host='.$databaseHost.';dbname='.$databaseName;
                self::$connection=new PDO(
                    $host,$user,$password,
                    array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
            }

        }

        /**
         * destructor
         */
        public function __destruct(){

            switch($this->storageType){
                case StorageType::FILE_SYSTEM : {
                    if ($this->fileHandle) {
                        fclose($this->fileHandle);
                    }
                } break;
                case StorageType::DATABASE : {
                    self::$connection = null;
                } break;
                default:{
                    throw new RuntimeException('Unknown storage type.');
                }
            }
        }

        /**
         * Save log on the file system
         *
         * @param $message
         * @throws RuntimeException
         */
        public function writeFS($message){

            if (null !== $this->fileHandle) {
                if (fwrite($this->fileHandle, $message) === false) {
                    throw new RuntimeException('The file could not be saved');
                } else {
                    $this->lastLine = trim($message);
                    $this->logLineCount++;
                }
            }
        }

        /**
         * Save log in the database
         *
         * @param $level
         * @param $message
         * @return int|string
         */
        public function writeDB($level,$message) {

            $query="INSERT INTO logs (`date`, `level`, `message`) VALUES (:date, :level, :messageDB)";
            $stmt=self::$connection->prepare($query);
            $date = $this->getTimestamp();
            $stmt->bindParam(":date", $date, PDO::PARAM_STR);
            $stmt->bindParam(":level", $level, PDO::PARAM_STR);
            $stmt->bindParam(":messageDB", $message, PDO::PARAM_STR);

            if($stmt->execute()){
                return self::$connection->lastInsertId();
            }
            else{
                return -1;
            }

        }

        private function prepareMessage($message, array $context = array()){
            if(empty($context)){
                return $message;
            }

            $replace = array();
            foreach ($context as $key => $val) {
                $replace['{' . $key . '}'] = $val;
            }

            /*
             * replacing placeholders
             */
            return strtr($message, $replace);
        }

        /**
         *
         * Returns curred date (formatted)
         * @return string
         */
        private function getTimestamp() {
            $originalTime = microtime(true);
            $micro = sprintf("%06d", ($originalTime - floor($originalTime)) * 1000000);
            $date = new DateTime(date('Y-m-d H:i:s.'.$micro, $originalTime));
            return $date->format('Y-m-d G:i:s.u');
        }

        /**
         * Returns formatted message for the log file/db
         *
         * @param $level
         * @param $message
         * @return string
         */
        public function formatMessage($level, $message){
            $message = "[{$this->getTimeStamp()}] [{$level}] {$message}";

            return $message.PHP_EOL;
        }

        /**
         * Concrete implementation
         *
         * @param $level
         * @param $message
         * @param array $context
         * @return mixed|void
         * @throws RuntimeException
         */
        public function log($level, $message, array $context = array()){

            switch($this->storageType){
                case StorageType::FILE_SYSTEM : {
                    $message = $this->prepareMessage($message,$context);
                    $message = $this->formatMessage($level, $message, $context);
                    $this->writeFS($message);
                } break;
                case StorageType::DATABASE : {
                    $message = $this->prepareMessage($message,$context);
                    $this->writeDB($level,$message);
                } break;
                default:{
                    throw new RuntimeException('Unknown storage type.');
                }
            }

        }

        /**
         * Used only inside of MyLogger.php
         * @param $storageType
         */
        private  function setStorageType($storageType) {
            $this->storageType = $storageType;
        }

        /**
         * @param $writeMode
         * @internal param  $fileHandle
         */
        public function setFileHandle($writeMode) {
            $this->fileHandle = fopen($this->logFilePath, $writeMode);
        }

        /**
         * @param string $stdOutPath
         */
        public function setLogToStdOut($stdOutPath) {
            $this->logFilePath = $stdOutPath;
        }


        /**
         * @param string $logDirectory
         */
        public function setLogFilePath($logDirectory) {
            $this->logFilePath = $logDirectory.DIRECTORY_SEPARATOR.'application_log_'.date('Y-m-d').'.'.'txt';
        }

    }
?>