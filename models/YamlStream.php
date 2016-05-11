<?php

/**
* Class to be registered as a stream Wrapper for parsing yaml files
* http://www.php.net/manual/en/class.streamwrapper.php
* @author jabi
*
*/
class Klear_Model_YamlStream
{
    protected $_protocol = 'klear.yaml://';

    protected $_file;
    protected $_position = 0;
    protected $_length;
    protected $_content = '';
    protected $_extraFiles = array();

    /*
     * http://www.php.net/manual/en/class.streamwrapper.php
     */
    function stream_open($path, $mode, $options, &$openedPath)
    {
        $options; //Avoid PMD UnusedFormalParameter warning
        $mode; //Avoid PMD UnusedFormalParameter warning

        $this->_file = $this->_getRealFilepath($path);
        $openedPath = $this->_file;

        $this->_content = $this->_loadContent($this->_file);

        $this->_resolveVariables();
        $this->_fixGettextInvocations();

        $this->_length = mb_strlen($this->_content, '8bit');

        $this->_position = 0;

        // Uncomment for debug
//         file_put_contents("/tmp/last", $this->_content);
        return true;
    }

    protected function _getRealFilepath($path)
    {
        $baseFile = str_replace($this->_protocol, '', $path);

        // TODO: sanitize file param
        $file = APPLICATION_PATH . '/configs/klear/' . trim($baseFile);

        if (!preg_match("/\.yaml$/", $file)) {
            $file .= '.yaml';
        }

        $file = realpath($file);

        if (false === $file  || !is_readable($file)) {
            throw new Zend_Exception('File not readable: ' . $baseFile);
        }

        return $file;
    }

    protected function _loadContent($file)
    {
        // Si no existe o no se puede leer devolvemos un string vacio
        if (!is_readable($file)) {
            return '';
        }
        $contents = '';

        $includeFiles = $this->_getIncludeFiles($file);
        foreach ($includeFiles as $includeFile) {
            $contents .= $this->_loadContent($includeFile);
            $this->_extraFiles[] = $includeFile;
        }

        $contents .= file_get_contents($file). "\n";
        return $contents;
    }

    protected function _getIncludeFiles($file)
    {
        $includeFiles = array();

        $fp = fopen($file, 'r');

        while ($line = fgets($fp)) {
            if (preg_match("/^\#include\s+([a-z0-9\/\._\-]+)/i", $line, $matches)) {
                $confFile = realpath(dirname($file) . DIRECTORY_SEPARATOR .  $matches[1]);

                if ($confFile) {
                    //Nos aseguramos de no incluir un mismo fichero 2 veces
                    $fileIncluded = array_search($confFile, $this->_extraFiles);
                    if ($fileIncluded === false) {
                        $includeFiles[] = $confFile;
                    }
                }

            } else {
                break;
            }
        }

        fclose($fp);

        return $includeFiles;
    }

    /**
     * @var string $path
     * @var array | object $data
     */
    protected function _walkDataSublevels($path, $data)
    {
        $pathSegments = explode(".", $path);
        $target = array_shift($pathSegments);
        $path = implode(".", $pathSegments);

        $nextLevel = null;

        switch (true) {

            case is_array($data):

                $nextLevel = array_key_exists($target, $data) ? $data[$target] : null;
                break;

            case is_object($data):

                $nextLevel = isset($data->$target) ? $data->$target : null;
                break;

            default:

                return null;
        }

        if (! is_scalar($nextLevel)) {

            return $this->_walkDataSublevels($path, $nextLevel);
        }

        return $nextLevel;
    }

    protected function _parseVariables($data)
    {
        switch(true) {
            case preg_match("/auth\.(.*)/", $data[1], $result):

                $auth = Zend_Auth::getInstance();

                if ($auth->hasIdentity() &&
                    ! is_null($this->_walkDataSublevels($result[1], $auth->getIdentity()))
                ) {
                    $value = $this->_walkDataSublevels($result[1], $auth->getIdentity());
                    
                    if (is_bool($value)) {
                        $value = $value? 'true':'false';
                    }
                    return $value;
                }
                break;

            case preg_match("/params\.(.*)/", $data[1], $result):
                $request = Zend_Controller_Front::getInstance()->getRequest();
                if (is_array($request)) {
                    $request = implode(',', $request);
                }
                return $request->getParam($result[1], '');
                break;

            case ($data[1] == 'lang'):
                    if (Zend_Registry::isRegistered('currentSystemLanguage')) {
                        return Zend_Registry::get('currentSystemLanguage')->getLanguage();
                    }
                    return 'es';
                break;
        }
        return '';
    }

    protected function _resolveVariables()
    {
        $this->_content = preg_replace_callback(
            '/\$\{([^\}]*)\}/',
            array($this, '_parseVariables'),
            $this->_content
        );
    }

    protected function _fixGettext($data) 
    {
        return ': "' . str_replace('"','\\"', $data[2]). '"';
    }

    protected function _fixGettextInvocations()
    {
        $this->_content = preg_replace_callback(
            '/:\s?(["\']{0,1})((_|ngettext)\(.*\))(["\']{0,1})/',
            array($this, '_fixGettext'),
            $this->_content
        );

    }


    public function stream_read($count)
    {
        $chunk = mb_substr($this->_content, $this->_position, $count, '8bit');
        $this->_position += $count;
        if ($this->_position > $this->_length) {
            $this->_position = $this->_length;
        }
        return $chunk;
    }

    public function stream_write($data)
    {
        $data; //Avoid PMD UnusedFormalParameter warning
        return false;
    }

    public function stream_tell()
    {
        return $this->_position;
    }

    public function stream_eof()
    {
        return $this->_position >= $this->_length;
    }

    public function stream_seek($offset, $whence)
    {
        switch ($whence) {
            case SEEK_SET:
                if ($offset < $this->_length && $offset >= 0) {
                    $this->_position = $offset;
                     return true;
                } else {
                    return false;
                }
                break;

            case SEEK_CUR:
                if ($offset >= 0) {
                     $this->_position += $offset;
                     return true;
                } else {
                     return false;
                }
                break;

            case SEEK_END:
                if ($this->_length  + $offset >= 0) {
                     $this->_position = $this->_length + $offset;
                     return true;
                } else {
                     return false;
                }
                break;

            default:
                return false;
        }
    }

    /**
     * TODO: The *real* array must be implemented
     * @return multitype:
     */
    public function stream_stat()
    {
        $stat = stat($this->_file);
        $stat[7] = $stat['size'] = $this->_length;

        return $stat;
    }

    public function url_stat($path)
    {
        $this->_file = $this->_getRealFilepath($path);
        return stat($this->_file);
    }

}
