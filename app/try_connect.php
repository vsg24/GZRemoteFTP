<?php
require_once __DIR__ . '/foundation.php';
header('Content-Type: application/json');
$error = false;
$msg = null;

$session_data = false;
$session_info = getConnectionInfoFromSession();

if($session_info != null)
{
    $session_data = true;

    $ftp_server = $session_info['ftp_server'];
    $port = $session_info['port'];
    $timeout = $session_info['timeout'];
}
else
{
    $ftp_server = isset($_POST['ftp_server']) ? $_POST['ftp_server'] : null;
    $port = isset($_POST['port']) ? $_POST['port'] : 21;
    $timeout = isset($_POST['timeout']) ? $_POST['timeout'] : 90;
}

if($ftp_server == null)
{
    $error = true;
    $msg = "FTP server address can not be empty.";
}

if(!$error)
{
    if(@ftp_connect($ftp_server, $port, $timeout) === false)
    {
        $error = true;
        $msg = "Could not open a connection to $ftp_server on port $port.";
    }
    else
    {
        if(!$session_data)
        {
            $_SESSION['ftp_server'] = $ftp_server;
            $_SESSION['port'] = $port;
            $_SESSION['timeout'] = $timeout;
        }
        $msg = "Successfully opened an FTP connection to $ftp_server on port $port.";
    }
}
else
{
    if($msg == null)
        $msg = "Unknown error while trying to open an FTP connection.";
}

$result = ['error' => $error, 'message' => $msg, 'time' => time()];
die(json_encode($result));