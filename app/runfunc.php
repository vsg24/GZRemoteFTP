<?php
require_once __DIR__ . '/foundation.php';
header('Content-Type: application/json');
$error = false;
$msg = null;

$session_data = false;
$session_info = getConnectionInfoFromSession();

$function = isset($_POST['function']) ? $_POST['function'] : '';
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

            if($function == 'ftp_nlist')
            {
                $msg = @ftp_nlist($conn, $param0);
            }
            elseif($function == 'ftp_chdir')
            {
                if(@ftp_chdir($conn, $param0))
                {
                    $msg = "Successfully changed directory. Current directory is " . @ftp_pwd($conn);
                }
                else
                {
                    $msg = "Failed to change current directory";
                }
            }
            elseif($function == 'ftp_pwd')
            {
                $msg = @ftp_pwd($conn);
            }
            elseif($function == 'ftp_chdir_nlist')
            {
                if(@ftp_chdir($conn, $param0))
                {
                    $msg = @ftp_nlist($conn, @ftp_pwd($conn));
                }
                else
                {
                    $msg = "Failed to change current directory";
                }
            }
            elseif($function == 'ftp_mkdir')
            {
                if(@ftp_mkdir($conn, $param0))
                {
                    $msg = "Successfully created the directory " . $param0;
                }
                else
                {
                    $msg = "Failed to create the directory " . $param0;
                }
            }
            elseif($function == 'ftp_rmdir')
            {
                if(@ftp_rmdir($conn, $param0))
                {
                    $msg = "Successfully removed the directory " . $param0;
                }
                else
                {
                    $msg = "Failed to remove the directory " . $param0;
                }
            }
            elseif($function == 'ftp_chdir_mkdir')
            {
                if(@ftp_chdir($conn, $param0))
                {
                    if(@ftp_mkdir($conn, $param1))
                    {
                        $msg = "Successfully created the directory " . $param1 . ' inside ' . @ftp_pwd($conn);
                    }
                    else
                    {
                        $msg = "Failed to create the directory " . $param1 . ' inside ' . @ftp_pwd($conn);
                    }
                }
                else
                {
                    $msg = "Failed to change current directory";
                }
            }
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