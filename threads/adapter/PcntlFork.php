<?php

namespace Thread\Adapter;

require_once 'Abstract.php';

class PcntlFork extends \Thread\Adapter\AdapterAbstract
{
    public function startThread($command, array $options = null)
    {
        $pid = pcntl_fork();

        if ($pid === -1) {
             throw new \Exception('Could not fork');
        }

        if (!$pid) {
            // child process

            if (isset($command['action']) && is_callable($command['action'])) {
                call_user_func_array($command['action'], $command);
            }
            die;
        }

        return $pid;
    }

    public function getThreadResponse($thread)
    {
    }

    public function closeThread($thread)
    {
    }

    public function prepareThreadCommand($params, $options)
    {
        return $params;
    }
}