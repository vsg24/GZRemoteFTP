<?php
require_once __DIR__ . '/foundation.php';
header('Content-Type: application/json');
$error = false;
$msg = null;

//function ftp_putAll($conn_id, $src_dir, $dst_dir) {
//    $d = dir($src_dir);
//    while($file = $d->read()) { // do this for each file in the directory
//        if ($file != "." && $file != "..") { // to prevent an infinite loop
//            if (is_dir($src_dir."/".$file)) { // do the following if it is a directory
//                if (!@ftp_chdir($conn_id, $dst_dir."/".$file)) {
//                    ftp_mkdir($conn_id, $dst_dir."/".$file); // create directories that do not yet exist
//                }
//                ftp_putAll($conn_id, $src_dir."/".$file, $dst_dir."/".$file); // recursive part
//            } else {
//                $upload = ftp_put($conn_id, $dst_dir."/".$file, $src_dir."/".$file, FTP_BINARY); // put the files
//            }
//        }
//    }
//    $d->close();
//}

function ftp_copy($conn_id, $src_dir, $dst_dir) {


    $d = dir($src_dir);

    while($file = $d->read()) {

        if ($file != "." && $file != "..") {

            if (is_dir($src_dir."/".$file)) {

                if (!@ftp_chdir($conn_id, $dst_dir."/".$file)) {

                    ftp_mkdir($conn_id, $dst_dir."/".$file);
                }

                ftp_copy($conn_id, $src_dir."/".$file, $dst_dir."/".$file);
            }
            else {

                $upload = ftp_put($conn_id, $dst_dir."/".$file, $src_dir."/".$file, FTP_BINARY);
            }
        }
    }

    $d->close();
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

            if(@ftp_chdir($conn, $param0))
            {
                // successfully changed directory

                ftp_copy($conn, $param1, $param0);

                $msg = "Upload was completed";
                goto END;
            }
            else
            {
                $msg = "Could not change the directory";
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

END:

$result = ['error' => $error, 'message' => $msg, 'time' => time()];
die(json_encode($result));