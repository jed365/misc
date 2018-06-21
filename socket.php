<?php
//Read the port number from first parameter on the command line if set
$port = (isset($argv[1])) ? intval($argv[1]) : 8080;

//Just a helper
function dlog($string) {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $string . "\n";
}

//Create socket
while (($sock = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
    dlog("socket_create() failed: reason: " . socket_strerror(socket_last_error()));
    sleep(1);
}

//Reduce blocking if previous connections weren't ended correctly
if (!socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1)) {
    dlog("socket_set_option() failed: reason: " . socket_strerror(socket_last_error($sock)));
    exit;
}

//Bind to port
$tries = 0;
while (@socket_bind($sock, 0, $port) === false) {
    dlog("socket_bind() failed: reason: " . socket_strerror(socket_last_error($sock)));
    sleep(1);
    $tries++;
    if ($tries>30) {
        dlog("socket_bind() failed 30 times giving up...");
        exit;
    }
}

//Start listening
while (@socket_listen($sock, 5) === false) {
    dlog("socket_listen() failed: reason: " . socket_strerror(socket_last_error($sock)));
    sleep(1);
}

//Makes it possible to accept several simultaneous connections
socket_set_nonblock($sock);

//Keeps track of active connections
$clients = array();

dlog("server started...");

while(true) {
    //Accept new connections
    while (($msgsock = @socket_accept($sock)) !== false) {
        //Prevent blocking
        socket_set_nonblock($msgsock);

        //Get IP - just for logging
        socket_getpeername($msgsock, $remote_address);

        //Add new client to array
        $clients[] = array('sock' => $msgsock, 'timeout' => time()+30, 'ip' => $remote_address);

        dlog("$remote_address connected, client count: ".count($clients));
    }
    //Loop existing clients and read input
    foreach($clients as $key => $client) {
        $rec = '';
        $buf = '';
        while (true) {
            //Read 2 kb into buffer
            $buf = socket_read($clients[$key]['sock'], 2048, PHP_BINARY_READ);

            //Break if error reading
            if ($buf === false) break;

            //Append buffer to input
            $rec .= $buf;

            //If no more data is available socket read returns an empty string - break
            if ($buf === '') break;
        }
        if ($rec=='') {
            //If nothing was received from this client for 30 seconds then end the connection
            if ($clients[$key]['timeout']<time()) {
                dlog('No data from ' . $clients[$key]['ip'] . ' for 30 seconds. Ending connection');

                //Close socket
                socket_close($client['sock']);

                //Clean up clients array
                unset($clients[$key]);
            }
        } else {
            //If something was received increase the timeout
            $clients[$key]['timeout']=time()+30;

            //And.... DO SOMETHING
            dlog('Raw data received from ' . $clients[$key]['ip'] . "\n------\n" . $rec . "\n------");
        }
    }

    //Allow the server to do other stuff by sleeping for 50 ms on each iteration
    usleep(50000);
}

//We'll never reach here, but some logic should be implemented to correctly end the server
foreach($clients as $key => $client) {
    socket_close($client['sock']);
}
@socket_close($sock);
exit;
