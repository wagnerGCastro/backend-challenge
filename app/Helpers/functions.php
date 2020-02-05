<?php

if(!function_exists('formatMessage')) {
    /**
     * Format message response
     * 
     * @param number $code
     * @param string $message
     * @return array
     *
     */
    function formatMessage($code, $message)
    {
        return ['code' => $code, 'message' => $message];
    }
}
