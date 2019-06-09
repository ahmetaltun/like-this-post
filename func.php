<?php
    function getIp() {

        if ( getenv( 'HTTP_CLIENT_IP' ) ) {
            $ip = getenv( 'HTTP_CLIENT_IP' );
        } elseif ( getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
            $ip = getenv( 'HTTP_X_FORWARDED_FOR' );
        } elseif ( getenv( 'HTTP_X_FORWARDED' ) ) {
            $ip = getenv( 'HTTP_X_FORWARDED' );
        } elseif ( getenv( 'HTTP_FORWARDED_FOR' ) ) {
            $ip = getenv( 'HTTP_FORWARDED_FOR' );
        } elseif ( getenv( 'HTTP_FORWARDED' ) ) {
            $ip = getenv( 'HTTP_FORWARDED' );
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }


        if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
            // Return local ip address
            return '127.0.0.1';
        } else {
            return $ip;
        }

    }
?>