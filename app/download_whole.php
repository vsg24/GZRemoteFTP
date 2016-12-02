<?php
require_once __DIR__ . '/foundation.php';
header('Content-Type: application/json');
$error = false;
$msg = null;

function ftp_sync ($conn_id, $dir) {

    if ($dir != ".") {
        if (@ftp_chdir($conn_id, $dir) == false) {
            $msg = "Could not change the directory";
            return;
        }
        if (!(is_dir($dir)))
            @mkdir($dir);
        @chdir ($dir);
    }

    $contents = ftp_nlist($conn_id, ".");
    foreach ($contents as $file) {

        if ($file == '.' || $file == '..')
            continue;

        if (@ftp_chdir($conn_id, $file)) {
            ftp_chdir ($conn_id, "..");
            ftp_sync ($conn_id, $file);
        }
        else
            ftp_get($conn_id, $file, $file, FTP_BINARY);
    }

    ftp_chdir ($conn_id, "..");
    chdir ("..");
}

$session_data = false;
$session_info = getConnectionInfoFromSession();

$param0 = isset($_POST['param0']) ? $_POST['param0'] : '.';
$param1 = isset($_POST['param1']) ? $_POST['param1'] : '.';

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

            ftp_sync($conn, $param1);

            $msg = "Download completed (files are saved at " . __DIR__ . ")";
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

END:

$result = ['error' => $error, 'message' => $msg, 'time' => time()];
die(json_encode($result));