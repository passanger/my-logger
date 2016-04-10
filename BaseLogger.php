<?php

    require_once('BaseLoggerInterface.php');
    /**
     * Simple logger that other Loggers can inherit from.
     */
    abstract class BaseLogger implements BaseLoggerInterface{

        /**
         * @param string $message
         * @param array  $context
         * @return null|void
         */
        public function warning($message, array $context = array())
        {
            $this->log(LogLevel::WARNING, $message, $context);
        }

        /**
         * @param string $message
         * @param array  $context
         * @return null|void
         */
        public function info($message, array $context = array())
        {
            $this->log(LogLevel::INFO, $message, $context);
        }

        /**
         * @param string $message
         * @param array $context
         * @return null|void
         */
        public function debug($message, array $context = array()){
            $this->log(LogLevel::DEBUG, $message, $context);
        }


        /**
         * @param string $message
         * @param array $context
         * @return null|void
         */
        public function error($message, array $context = array()){
            $this->log(LogLevel::ERROR, $message, $context);
        }
    }

?>