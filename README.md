```php
<?php

require_once 'threads/ThreadManager.php';

$thrManager = Thread\ThreadManager::factory(array(
    'timeout'            => 10 * 60, // seconds
    'maxProcess'         => 10,
    'scriptPath'         => dirname(__FILE__) . '/worker.php', // path to worker script
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

someOnAllCompliteCallback();

```
