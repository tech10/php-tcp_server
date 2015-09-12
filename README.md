# Simple TCP chat server written using streams.

This server requires at least php 5.4, and is meant to run on the command line, So far, it's only been tested on Linux, but seems to be fairly stable. So far, you can connect to the server using telnet or any program that uses a basic text protocol, and sends no information upon connect. With telnet, any non-printable characters are removed, any ASCII character below 32 and above 127, I believe.

## Examples

Here is a brief example of how to run it on Linux.

Change to the directory where you cloned the repository, then execute the following command.

    ./server.php

By default, the server binds to all IPV4 and IPV6 addresses, and is available at port 6000. For help on the available commands within the server, type /help. To terminate the server, CTRL+C can be used. Look at ./core/functions/signal.php for more information on the Posix signals used to terminate the server and restart it.

## Extending

For the moment, extending the server is fairly basic. Create a plugins directory within the main directory where you cloned the repository. Add any php scripts within that directory, or for more complex plugins or those with their own licenses, create a directory, then a php script with the name of that directory. For example, say you had a directory for an encryption plugin called encrypt. The plugin would go in ./plugins/encrypt/encrypt.php, and from there, would include all of its other needed files and directories. So far, this would only be useful to add custom functions, classes and the like. Any custom events aren't yet available to be run.

## Contributing

Contributions are welcome, but please stay within the existing coding standards within the files, no extreme modification of the code layout. Keep variable standards defined as they currently are, and if defining other variables or creating other functions, keep the layout similar to what it is already.

Please note that the code may undergo extreme changes, since I do have several ideas of my own on how to make it better than it is at the moment.

Of course, you're free to fork repositories and submit pull requests, I'm more than willing to take a look at any changes people may wish to make. You're also free to fork this repository and dramatically alter anything you like within the terms of the license, located in LICENSE.txt.