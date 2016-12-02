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
    $username = $session_info['username'];
    $password = $session_info['password'];
}
else
{
    $ftp_server = isset($_POST['ftp_server']) ? $_POST['ftp_server'] : null;
    $port = isset($_POST['port']) ? $_POST['port'] : 21;
    $timeout = isset($_POST['timeout']) ? $_POST['timeout'] : 90;
    $username = isset($_POST['username']) ? $_POST['username'] : null;
    $password = isset($_POST['password']) ? $_POST['password'] : null;
}

if($ftp_server == null)
{
    $error = true;
    $msg = "FTP server address can not be empty.";
}
if($username == null || $password == null)
{
    $error = true;
    $msg = "Username or password is null";
}

if(!$error)
{
    $conn = @ftp_connect($ftp_server, $port, $timeout);
    if($conn === false)
    {
        $error = true;
        $msg = "Could not open a connection to $ftp_server on port $port.";
    }
    else
    {
        $login_result = @ftp_login($conn, $username, $password);
        if($login_result)
        {
            if(!$session_data)
            {
                $_SESSION['ftp_server'] = $ftp_server;
                $_SESSION['port'] = $port;
                $_SESSION['timeout'] = $timeout;
                $_SESSION['username'] = $username;
                $_SESSION['password'] = $password;
            }
            $msg = "Successfully authenticated on $ftp_server on port $port.";
        }
        else
        {
            $error = true;
            $msg = "Log in failed.";
        }
    }
}
else
{
    if($msg == null)
        $msg = "Unknown error while trying to open an FTP connection.";
}

$result = ['error' => $error, 'message' => $msg, 'time' => time()];
die(json_encode($result));