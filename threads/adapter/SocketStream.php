<?php

require_once 'Abstract.php';

class SocketStream extends ThreadAdapterAbstract
{
    public function startThread($command, array $options = null)
    {
        $url     = $this->_parseUrl($options);
        $process = stream_socket_client("tcp://{$url['host']}:{$url['port']}", $errno, $errstr, isset($options['timeout']) ? $options['timeout'] : 0);
        stream_set_blocking($process, false);
        fwrite($process, $command);
        return $process;
    }

    public function getThreadResponse($thread)
    {
    	$response = parent::getThreadResponse($thread);
    	if ($response) {
    	    $response = explode("\r\n\r\n", $response, 2); // отрезаем хедеры
    	    return end($response);
    	}
    	return $response;
    }

    public function closeThread($thread)
    {
    	fclose($thread);
    }

    public function prepareThreadCommand($params, $options)
    {
        $url     = $this->_parseUrl($options, $params);
        $process = !empty($options['process']) ? $options['process'] : 'php';

        return "GET {$url['path']}?". http_build_query($url['query']) ." HTTP/1.0\r\nHost: {$url['host']}\r\n\r\n";
    }


    /* PRIVATE */

    private function _parseUrl(array $options, $params = null)
    {
        $url = isset($options['threadUrl']) ? $options['threadUrl'] : null;

        if (!$url) {
            throw new Exception('Неверно указан скрипт для запуска процессов');
        }

        $url = array_merge(array(
                'scheme' => 'http',
                'host'   => @$_SERVER['HTTP_HOST'],
                'port'   => '80',
                'path'   => '/',
                'query'  => array()
            ),
            parse_url($url)
        );

        if (!empty($url['query'])) {
            parse_str($url['query'], $q);
            $url['query'] = (array) $q;
        }

        $url['query'] = array_merge($url['query'], (array) $params);

        return $url;
    }
}