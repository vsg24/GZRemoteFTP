<?php

function getConnectionInfoFromSession()
{
    if(isset($_SESSION['ftp_server']) && isset($_SESSION['port']))
    {
        $result = [];

        $result['ftp_server'] = isset($_SESSION['ftp_server']) ? $_SESSION['ftp_server'] : null;
        $result['port'] = isset($_SESSION['port']) ? $_SESSION['port'] : null;
        $result['timeout'] = isset($_SESSION['timeout']) ? $_SESSION['timeout'] : null;
        $result['username'] = isset($_SESSION['username']) ? $_SESSION['username'] : null;
        $result['password'] = isset($_SESSION['password']) ? $_SESSION['password'] : null;

        foreach ($result as $res)
        {
            if($res == null)
            {
                return null;
            }
        }

        return $result;
    }

    return null;
}

function clearConnectionInfoFromSession()
{
    unset($_SESSION['ftp_server']);
    unset($_SESSION['port']);
    unset($_SESSION['username']);
    unset($_SESSION['password']);
    unset($_SESSION['timeout']);
}

function autoRedirectTo($location)
{
    if( $location != null ) {
        ob_end_clean();
        header("Location: {$location}");
        exit;
    }
    return;
}