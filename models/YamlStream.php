<?php

/**
* Class to be registered as a stream Wrapper for parsing yaml files
* @author jabi
*
*/
class Klear_Model_YamlStream {

    protected $_protocol = 'klear.yaml://';

    protected $_openedPath;
    protected $_file;
    protected $_position = 0;
    protected $_length;
    protected $_content = '';
    protected $_extraFiles = array();

    function stream_open($path, $mode = 'r', $options, &$opened_path)
    {

        $baseFile = str_replace($this->_protocol,'',$path);

        // TODO: sanitize file param

        $file = APPLICATION_PATH . '/configs/klear/' . trim($baseFile);


        if (!preg_match("/\.yaml$/",$file)) $file .= '.yaml';

        if ( (!file_exists($file)) || (!is_readable($file)) ) {
            Throw new Zend_Exception('File not readable');
        }

        $this->_openedPath = dirname($file);
        $this->_file = $file;


        $fp = fopen($this->_file, 'r');

        while ($line = fgets($fp)) {

            if (preg_match("/^\#include\s+([a-z0-9\/\._\-]+)/i", $line, $matches))
            {

                $confFile = $this->_openedPath . '/' .  $matches[1];

                if (file_exists($confFile)) {
                    $this->_extraFiles[] = $confFile;
                }

            } else {
                break;
            }
        }

        fclose($fp);

        $this->_content = '';
        foreach ($this->_extraFiles as $_confFile) {
            $this->_content .= file_get_contents($_confFile) ."\n";
        }


        $this->_content .= file_get_contents($this->_file);

        $this->_resolveVariables();

        $this->_length = mb_strlen($this->_content);

        $this->_position = 0;

        // Uncomment for debug
        //file_put_contents("/tmp/last",$this->_content);
        return true;
    }

    protected function _parseVariables($data)
    {
        switch(true) {
            case preg_match("/auth\.(.*)/", $data[1], $result):
                $auth = Zend_Auth::getInstance();
                if ( ($auth->hasIdentity()) &&
                    (isset($auth->getIdentity()->{$result[1]})) ) {

                    return $auth->getIdentity()->{$result[1]};
                }

                break;
            case preg_match("/params\.(.*)/", $data[1], $result):

                $request = Zend_Controller_Front::getInstance()->getRequest();
                return $request->getParam($result[1],'');
                
                break;
            case ($data[1] == 'lang'):
                    // TODO: pickup language
                    return 'es';
                break;

        }

        return '';
    }

    protected function _resolveVariables()
    {
        $this->_content = preg_replace_callback(
            '/\$\{([^\}]*)\}/',
            array($this,'_parseVariables'),
            $this->_content
        );
    }

    public function stream_read($count)
    {
        $chunk = mb_substr($this->_content, $this->_position, $count);
        $this->_position += $count;
        if ($this->_position > $this->_length) $this->_position = $this->_length;
        return $chunk;
    }

    public function stream_write($data)
    {
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

}


