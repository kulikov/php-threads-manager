<?php

require_once 'adapter/Interface.php';

class ThreadManager
{
    private
        $_options            = array(
            'timeout'            => 60,
            'scriptPath'         => null,
            'process'            => 'php',
            'maxProcess'         => 10,
            'onCompliteCallback' => null,
            'adapter'            => 'UnixProcess',
        ),
        $_adapter          = null,
        $_runningProcesses = array(),
        $_threadQueue      = array();


    /**
     * —оздает менеджер тредов.
     * ѕринимает настройки, список которых можно посмотреть в $this->_options
     *
     * @return ThreadManager
     */
    public static function factory(array $options = null)
    {
        $instance = new self;

        if ($options) {
            $instance->_options = array_merge($instance->_options, $options);
        }

        return $instance;
    }

    public function setCompliteCallback($callback)
    {
    	$this->_options['onCompliteCallback'] = $callback;
    	return $this;
    }

    public function setAdapter(ThreadAdapterInterface $adapter)
    {
    	$this->_adapter = $adapter;
    	return $this;
    }

    /**
     * ƒобавл€ет в очередь задание
     *
     * @param $requestParams array массив параметров, которые будут переданы процессу
     * @return ThreadManager
     */
    public function addThread($requestParams = null)
    {
        $this->_threadQueue[] = $this->_createThreadCommand($requestParams);
        return $this;
    }

    /**
     * «апускает выполнение всех тредов.
     * ћожно ограничить количество одновременно работающих потоков
     * через опцию maxProcess. ѕо-умолчинию не более 10
     */
    public function run()
    {
        $maxProcess = $this->_getOption('maxProcess');
        $count      = 0;

        foreach ($this->_threadQueue as $i => $thread) {
            if ($count < $maxProcess) {
                $this->_runProcess($thread);
                unset($this->_threadQueue[$i]);
            }
            $count++;
        }

        $this->_startIterations();

        return $this;
    }



    /* PRIVATE */

    private function __construct()
    {
    }

    private function _runProcess($command)
    {
        $this->_runningProcesses[] = $this->_getAdapter()->startThread($command, $this->_options);
        return $this;
    }

    private function _startIterations()
    {
        $_startTime = microtime(true);
        $_timeout   = $this->_getOption('timeout');
        $adapter    = $this->_getAdapter();

        // пока есть активные процессы
        while ($this->_runningProcesses) {

            // если превышен общий таймаут выполнени€ прибиваем оставшиес€ невыполненные задани€
            if ($_timeout && (microtime(true) - $_startTime) > $_timeout) {
                foreach ($this->_runningProcesses as $i => $thread) {
                    $adapter->closeThread($thread); // закрываем процесс
                    unset($this->_runningProcesses[$i]);  // удал€ем его из списка активных
                }
            }

            // перебираем их и ждем пока не придет ответ
            foreach ($this->_runningProcesses as $i => $thread) {

                // если ответ пришел или процесс завершил работу
                $response = $adapter->getThreadResponse($thread);

                if ($response !== false) {

                    $adapter->closeThread($thread); // закрываем процесс
                    unset($this->_runningProcesses[$i]);  // удал€ем его из списка активных

                    $this->_notifyComplite($response); // уведомл€ем клиента и передаем ему ответ процесса


                    /**
                     * ≈сли в очереди еще остались задачи дл€ выполнени€
                     * и не превышено общее врем€ выполнени€ Ч†запускаем еще один процесс из очереди в стек активных
                     */
                    if ($this->_threadQueue && !($_timeout && (microtime(true) - $_startTime) > $_timeout)) {

                        $nextThread = array_shift($this->_threadQueue);
                        $this->_runProcess($nextThread);
                    }
                }
            }

            usleep(10000); // 0.01 секунды задержка в выполнении цикла. слишком часто спрашивать ответ не об€зательно
        }
    }

    private function _notifyComplite($response)
    {
        $callback = $this->_getOption('onCompliteCallback');

        if ($callback && is_callable($callback)) {
            call_user_func($callback, $response);
        }
    }

    private function _createThreadCommand($params = null)
    {
        return $this->_getAdapter()->prepareThreadCommand($params, $this->_options);
    }


    /**
     * @return ThreadAdapterInterface
     */
    private function _getAdapter()
    {
        if ($this->_adapter === null) {
            $name = $this->_getOption('adapter');
            require_once 'adapter/'. $name .'.php';
            $this->_adapter = new $name();
        }
        return $this->_adapter;
    }

    private function _getOption($name)
    {
    	return isset($this->_options[$name]) ? $this->_options[$name] : null;
    }
}