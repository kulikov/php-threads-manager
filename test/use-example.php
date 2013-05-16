<?php

require_once __DIR__ . '/../threads/ThreadManager.php';

$thrManager = Thread\ThreadManager::factory(array(
    'timeout'            => 10 * 60, // seconds
    'maxProcess'         => 10,
    'scriptPath'         => __DIR__ . '/worker.php', // path to worker script
    'onCompliteCallback' => function($response) {
        print '<pre>';
        print_r($response); //
    }
));


for ($i = 0; $i < 30; $i++) {
    $thrManager->addThread(array('action' => 'test', 'data' => 'Hello, world!', 'id' => $i));
}

$thrManager->run(); // run it!

print("All processes finished on this line!");


die;


/**


declare(ticks = 1);

$t = microtime(true);

$nn = array();


class SignalHandlerContext
{
    public
        $run    = true,
        $buffer = array();

    public function callback()
    {
        print '<pre>';
        print_r($this->buffer);
    	print "\n\n";
        exit(0);
    }
}

$handlerContext = new SignalHandlerContext();
pcntl_signal(SIGTERM, array($handlerContext, 'callback'));

$data = 'hellow world!';

for ($i=0; $i < 10; $i++) {

    $pid = pcntl_fork();

    if ($pid === -1) {
         throw new Exception('Could not fork');
    }

    if (!$pid) {
        // child process

        sleep(rand(1, 10) / 10);

        $data .= ':' . $i;
        file_put_contents(dirname(__FILE__) . '/tt.txt', $i . ' - ' . $data . "\n\n", FILE_APPEND);
        posix_kill(getmypid(), SIGTERM);
        die;
    }

    $handlerContext->buffer[] = $pid;
    print microtime(true) - $t;
    print "\n\n";

}


//while ($handlerContext->run) {
//    print '<pre>';
//    print_r($handlerContext->buffer);
//    print '- ';
//    usleep(20000);
//}

print microtime(true) - $t;
print "\n\n";
die;

*/
