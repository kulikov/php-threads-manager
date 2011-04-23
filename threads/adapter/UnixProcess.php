<?php

require_once 'Abstract.php';

class UnixProcess extends ThreadAdapterAbstract
{
    public function startThread($command, array $options = null)
    {
        $process = popen($command, 'r');
        stream_set_blocking($process, false);
        return $process;
    }

    public function closeThread($thread)
    {
    	pclose($thread);
    }

    public function prepareThreadCommand($params, $options)
    {
    	$scriptPath = isset($options['scriptPath']) ? $options['scriptPath'] : null;
        $process    = !empty($options['process']) ? $options['process'] : 'php';

        if (!$scriptPath || !file_exists($scriptPath)) {
            throw new Exception('Неверно указан скрипт для запуска процессов');
        }

        $args = str_replace('&', '\\&', http_build_query((array) $params));

        return "{$process} {$scriptPath} {$args} &";
    }
}