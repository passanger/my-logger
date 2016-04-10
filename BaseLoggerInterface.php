<?php

    /**
     * BaseLoggerInterface describes a logger instance
     *
     * Note:
     * $context array is used only for placeholders
     */
    interface BaseLoggerInterface{


        /**
         * Some events.
         *
         * @param string $message
         * @param array  $context
         * @return null
         */
        public function info($message, array $context = array());

        /**
         *  Debug information.
         *
         * @param string $message
         * @param array  $context
         * @return null
         */
        public function debug($message, array $context = array());

        /**
         * Runtime errors that do not require immediate action
         *
         * @param string $message
         * @param array  $context
         * @return null
         */
        public function error($message, array $context = array());


        /**
         * Logs with an arbitrary level.
         *
         * @param $level
         * @param $message
         * @param array $context
         * @return mixed
         */
        public function log($level, $message, array $context = array());
    }
?>