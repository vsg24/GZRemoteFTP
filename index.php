<?php
require_once __DIR__ . '/app/foundation.php';
$session = getConnectionInfoFromSession();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <title>GZ RemoteFTP</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap-flex.min.css">
    <link rel="stylesheet" href="css/movingBallG.css">
    <style>
        .red {
            color: red;
        }
    </style>
</head>
<body>
<div class="container">

    <div class="row">
        <div class="col-lg-7">
            <br>
            <div class="float-xs-left">
                <h1>GZ RemoteFTP</h1>
            </div>
            <?php if(getConnectionInfoFromSession() != null) : ?>
                <div class="float-xs-right">
                    <h6 class="red">Session exists - server: <?php echo @$session['ftp_server'] ?></h6>
                    <a href="/app/clearsession.php" class="btn btn-sm btn-danger">Clear</a>
                </div>
            <?php endif; ?>
            <div class="clearfix"></div>
            <br>
            <form method="post">
                <section id="server_info">
                    <div class="form-group">
                        <label for="ftp_server">FTP server address</label>
                        <input type="text" class="form-control" id="ftp_server" name="ftp_server" value="<?php echo @$session['ftp_server'] ?>" placeholder="ftp.example.com or 192.168.1.1">
                    </div>
                </section>
                <section id="login_credentials" style="display: none">
                    <div class="form-group">
                        <label for="username">Username (User login)</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo @$session['username'] ?>" placeholder="admin">
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" value="<?php echo @$session['password'] ?>" placeholder="password123">
                    </div>
                </section>
                <section id="advanced_params">
                    <div class="form-group">
                        <label for="port">FTP server port</label>
                        <input type="number" class="form-control" id="port" value="21" placeholder="port of the FTP server">
                    </div>
                    <div class="form-group">
                        <label for="connection_timeout">Keep connection open for (seconds):</label><br>
                        <label class="form-check-inline">
                            <input class="form-check-input" type="radio" name="connection_timeout" checked value="90"> 90
                        </label>
                        <label class="form-check-inline">
                            <input class="form-check-input" type="radio" name="connection_timeout" value="180"> 180
                        </label>
                        <label class="form-check-inline">
                            <input class="form-check-input" type="radio" name="connection_timeout" value="270"> 270
                        </label>
                    </div>
                </section>
                <section id="toolbox" style="display: none">
                    <button type="button" id="get_files" class="btn btn-lg">Get files and directories</button>
                    <button type="button" id="change_dir" class="btn btn-lg">Change directory</button>
                    <button type="button" id="change_dir_get_files" class="btn btn-lg">Change directory and get files</button>
                    <button type="button" id="current_dir" class="btn btn-lg">Current directory</button>
                    <button type="button" id="upload_whole_dir" class="btn btn-lg">Upload whole directory</button>
                    <button type="button" id="download_whole_dir" class="btn btn-lg">Download whole directory</button>
                    <button type="button" id="make_dir" class="btn btn-lg">Make directory</button>
                    <button type="button" id="change_dir_mkdir" class="btn btn-lg">Change directory and create new folder</button>
                    <button type="button" id="remove_dir" class="btn btn-lg">Remove directory</button>
                    <br>
                    <div class="form-group">
                        <label for="param_z">Param 0</label>
                        <input type="text" id="param_z" class="form-control" placeholder=". or directory name">
                    </div>
                    <div class="form-group">
                        <label for="param_o">Param 1</label>
                        <input type="text" id="param_o" class="form-control" placeholder=". or directory name">
                    </div>
                    <br>
                </section>
                <div class="text-xs-center">
                    <button type="button" id="try_to_connect" class="btn btn-primary">Try to connect</button>
                    <button type="button" id="connect" class="btn btn-primary" style="display: none">Connect</button>
                    <button type="button" id="clear_logs" class="btn btn-secondary">Clear logs</button>
                </div>
                <div class="form-group">
                    <label for="log">Action log:</label>
                    <textarea class="form-control" rows="10" id="log" readonly="readonly"></textarea>
                </div>

                <div id="loading-container">
                    <br>
                    <div id="movingBallG">
                        <div class="movingBallLineG"></div>
                        <div id="movingBallG_1" class="movingBallG"></div>
                    </div>
                    <br>
                </div>
            </form>
        </div>
        <div class="col-lg-5">
            <br>
            <h3>Notes:</h3>
            <ul>
                <li>In cases of upload and chdir+mkdir, `Param 0` is the destination on the remote server.</li>
                <li>In case of upload `Param 1` is the directory on the server that is running this script. It's the source directory.</li>
                <li>In case of chdir+mkdir, `Param 1` is the name of the new directory to be created.</li>
                <li>In case of download, `Param 0` is the destination in the server that is running this script and `Param 1` is the source from which the
                    script will download the files.</li>
                <li>In most other cases, `Param 1` is <b>NOT</b> used at all</li>
            </ul>
            <h4>Some useful directory related info</h4>
            <ul>
                <li><code>.</code> => <code><?php echo __DIR__ ?></code></li>
                <li><code>..</code> => <code><?php echo dirname(__DIR__) ?></code></li>
                <li>If you include your file/folder (s) besides the <code>index.php</code> of this script, for referencing them you should do: <code>
                        ../example_folder</code></li>
            </ul>
            <h4>Removing non empty directories</h4>
            <div>
                Unfortunately it's not possible to remove these kind of directories without some additional software. In Linux you can install
                <code>lftp</code> and login using:<br>
                <code>$ lftp -u "username","pass" "server"</code><br>and then you can remove the non empty directory using:<br><code>$ lftp> rm -r
                    "directory"</code><br>
                Visit <a href="http://serverfault.com/questions/411970/how-to-avoid-lftp-certificate-verification-error">here</a> if you get any certificate
                error.
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg">
            <footer>
                <br>
                2016/12 - Created by <a href="http://atvsg.com">Vahid Amiri Motlagh</a> (vsg24), <a href="https://github.com/vsg24">github.com/vsg24</a>
                <br>
                Available free of charge for all kinds of uses, You take full responsibility by using this software
            </footer>
            <br>
        </div>
    </div>

</div><!-- /.container -->

<!-- jQuery first, then Tether, then Bootstrap JS. -->
<script src="js/jquery-3.1.1.min.js"></script>
<script src="js/tether.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script>
    $('#loading-container').hide();

    var tryConnectButton = $('#try_to_connect');
    var connectButton = $('#connect');
    var log = $('#log');

    var newline = '\n';

    $('#clear_logs').click(function () {
        log.empty();
    });

    tryConnectButton.click(function (e) {
        $('#loading-container').show();
        $.ajax({
            type: 'POST',
            url: '/app/try_connect.php',
            data: {
                'ftp_server': $('#ftp_server').val(),
                'port': $('#port').val()
            },
            dataType: 'json'
        }).done(function(data) {
            $('#loading-container').hide();
            if(data != null)
            {
                goforAuth();
                tryConnectButton.hide();
                connectButton.show();
                log.append(newline + data.message);
            }
            else
            {
                log.append(data.message);
                console.log('failure');
                console.log(data.errors);
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            $('#loading-container').hide();
            console.log('ajax failed');
            console.log(jqXHR);
        });
        e.preventDefault();
    });

    connectButton.click(function (e) {
        $('#loading-container').show();
        $.ajax({
            type: 'POST',
            url: '/app/connect.php',
            data: {
                'ftp_server': $('#ftp_server').val(),
                'port': $('#port').val(),
                'username': $('#username').val(),
                'password': $('#password').val()
            },
            dataType: 'json'
        }).done(function(data) {
            $('#loading-container').hide();
            if(data != null)
            {
                $('#server_info').hide();
                $('#login_credentials').hide();
                $('#advanced_params').hide();
                connectButton.hide();
                $('#toolbox').show();
                log.append(newline + data.message);
            }
            else
            {
                log.append(data.message);
                console.log('failure');
                console.log(data.errors);
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            $('#loading-container').hide();
            console.log('ajax failed');
            console.log(jqXHR);
        });
        e.preventDefault();
    });

    $('#get_files').click(function (e) {
        $('#loading-container').show();
        $.ajax({
            type: 'POST',
            url: '/app/runfunc.php',
            data: {
                'function': 'ftp_nlist',
                'param0': $('#param_z').val(),
                'ftp_server': $('#ftp_server').val(),
                'port': $('#port').val(),
                'username': $('#username').val(),
                'password': $('#password').val()
            },
            dataType: 'json'
        }).done(function(data) {
            $('#loading-container').hide();
            if(data != null)
            {
                console.log(data);
                log.append(newline + 'Listing directories and files');
                for(var i = 0; i <= data.message.length; i++)
                {
                    if(data.message[i] == undefined)
                        continue;
                    log.append(newline + data.message[i]);
                }
            }
            else
            {
                log.append(data.message);
                console.log('failure');
                console.log(data.errors);
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            $('#loading-container').hide();
            console.log('ajax failed');
            console.log(jqXHR);
        });
        e.preventDefault();
    });

    $('#change_dir').click(function (e) {
        $('#loading-container').show();
        $.ajax({
            type: 'POST',
            url: '/app/runfunc.php',
            data: {
                'function': 'ftp_chdir',
                'param0': $('#param_z').val(),
                'ftp_server': $('#ftp_server').val(),
                'port': $('#port').val(),
                'username': $('#username').val(),
                'password': $('#password').val()
            },
            dataType: 'json'
        }).done(function(data) {
            $('#loading-container').hide();
            if(data != null)
            {
                log.append(newline + data.message);
            }
            else
            {
                log.append(data.message);
                console.log('failure');
                console.log(data.errors);
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            $('#loading-container').hide();
            console.log('ajax failed');
            console.log(jqXHR);
        });
        e.preventDefault();
    });

    $('#current_dir').click(function (e) {
        $('#loading-container').show();
        $.ajax({
            type: 'POST',
            url: '/app/runfunc.php',
            data: {
                'function': 'ftp_pwd',
                'param0': $('#param_z').val(),
                'ftp_server': $('#ftp_server').val(),
                'port': $('#port').val(),
                'username': $('#username').val(),
                'password': $('#password').val()
            },
            dataType: 'json'
        }).done(function(data) {
            $('#loading-container').hide();
            if(data != null)
            {
                if(data.message != false)
                {
                    log.append(newline + 'Current directory is');
                    log.append(newline + data.message);
                }
                else
                {
                    log.append(newline + 'Failed to get the current directory');
                }
            }
            else
            {
                log.append(data.message);
                console.log('failure');
                console.log(data.errors);
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            $('#loading-container').hide();
            console.log('ajax failed');
            console.log(jqXHR);
        });
        e.preventDefault();
    });

    $('#change_dir_get_files').click(function (e) {
        $('#loading-container').show();
        $.ajax({
            type: 'POST',
            url: '/app/runfunc.php',
            data: {
                'function': 'ftp_chdir_nlist',
                'param0': $('#param_z').val(),
                'ftp_server': $('#ftp_server').val(),
                'port': $('#port').val(),
                'username': $('#username').val(),
                'password': $('#password').val()
            },
            dataType: 'json'
        }).done(function(data) {
            $('#loading-container').hide();
            if(data != null)
            {
                if(typeof data.message !== 'string')
                {
                    log.append(newline + 'Listing directories and files');
                    for(var i = 0; i <= data.message.length; i++)
                    {
                        if(data.message[i] == undefined)
                            continue;
                        log.append(newline + data.message[i]);
                    }
                }
                else
                {
                    log.append(newline + data.message);
                }
            }
            else
            {
                log.append(data.message);
                console.log('failure');
                console.log(data.errors);
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            $('#loading-container').hide();
            console.log('ajax failed');
            console.log(jqXHR);
        });
        e.preventDefault();
    });

    $('#upload_whole_dir').click(function (e) {
        $('#loading-container').show();
        $.ajax({
            type: 'POST',
            url: '/app/upload_whole.php',
            data: {
                'param0': $('#param_z').val(),
                'param1': $('#param_o').val(),
                'ftp_server': $('#ftp_server').val(),
                'port': $('#port').val(),
                'username': $('#username').val(),
                'password': $('#password').val()
            },
            dataType: 'json'
        }).done(function(data) {
            $('#loading-container').hide();
            if(data != null)
            {
                log.append(newline + data.message);
            }
            else
            {
                log.append(data.message);
                console.log('failure');
                console.log(data.errors);
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            $('#loading-container').hide();
            console.log('ajax failed');
            console.log(jqXHR);
        });
        e.preventDefault();
    });

    $('#download_whole_dir').click(function (e) {
        $('#loading-container').show();
        $.ajax({
            type: 'POST',
            url: '/app/download_whole.php',
            data: {
                'param0': $('#param_z').val(),
                'param1': $('#param_o').val(),
                'ftp_server': $('#ftp_server').val(),
                'port': $('#port').val(),
                'username': $('#username').val(),
                'password': $('#password').val()
            },
            dataType: 'json'
        }).done(function(data) {
            $('#loading-container').hide();
            if(data != null)
            {
                log.append(newline + data.message);
            }
            else
            {
                log.append(data.message);
                console.log('failure');
                console.log(data.errors);
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            $('#loading-container').hide();
            console.log('ajax failed');
            console.log(jqXHR);
        });
        e.preventDefault();
    });

    $('#make_dir').click(function (e) {
        $('#loading-container').show();
        $.ajax({
            type: 'POST',
            url: '/app/runfunc.php',
            data: {
                'function': 'ftp_mkdir',
                'param0': $('#param_z').val(),
                'ftp_server': $('#ftp_server').val(),
                'port': $('#port').val(),
                'username': $('#username').val(),
                'password': $('#password').val()
            },
            dataType: 'json'
        }).done(function(data) {
            $('#loading-container').hide();
            if(data != null)
            {
                log.append(newline + data.message);
            }
            else
            {
                log.append(data.message);
                console.log('failure');
                console.log(data.errors);
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            $('#loading-container').hide();
            console.log('ajax failed');
            console.log(jqXHR);
        });
        e.preventDefault();
    });

    $('#remove_dir').click(function (e) {
        $('#loading-container').show();
        $.ajax({
            type: 'POST',
            url: '/app/runfunc.php',
            data: {
                'function': 'ftp_rmdir',
                'param0': $('#param_z').val(),
                'ftp_server': $('#ftp_server').val(),
                'port': $('#port').val(),
                'username': $('#username').val(),
                'password': $('#password').val()
            },
            dataType: 'json'
        }).done(function(data) {
            $('#loading-container').hide();
            if(data != null)
            {
                log.append(newline + data.message);
            }
            else
            {
                log.append(data.message);
                console.log('failure');
                console.log(data.errors);
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            $('#loading-container').hide();
            console.log('ajax failed');
            console.log(jqXHR);
        });
        e.preventDefault();
    });

    $('#change_dir_mkdir').click(function (e) {
        $('#loading-container').show();
        $.ajax({
            type: 'POST',
            url: '/app/runfunc.php',
            data: {
                'function': 'ftp_chdir_mkdir',
                'param0': $('#param_z').val(),
                'param1': $('#param_o').val(),
                'ftp_server': $('#ftp_server').val(),
                'port': $('#port').val(),
                'username': $('#username').val(),
                'password': $('#password').val()
            },
            dataType: 'json'
        }).done(function(data) {
            $('#loading-container').hide();
            if(data != null)
            {
                log.append(newline + data.message);
            }
            else
            {
                log.append(data.message);
                console.log('failure');
                console.log(data.errors);
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            $('#loading-container').hide();
            console.log('ajax failed');
            console.log(jqXHR);
        });
        e.preventDefault();
    });

    function goforAuth() {
        $('#login_credentials').show();
    }
</script>
</body>
</html>