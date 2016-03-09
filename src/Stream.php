<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Opis\Encoding;

/**
 * Description of Stream
 *
 * @author cylex
 */
class Stream
{
    const EOF = null;
    protected $buffer;
    protected $pointer;
    protected $length;

    public function __construct($buffer = '')
    {
        $this->buffer = $buffer;
        $this->length = strlen($buffer);
        $this->pointer = 0;
    }

    public function read()
    {
        if ($this->pointer >= $this->length) {
            return static::EOF;
        }
        
        return $this->buffer[$this->pointer++];
    }

    public function write($data, $prepend = false)
    {
        if ($prepend) {
            $this->buffer = $data . substr($this->buffer, $this->pointer);
            $this->pointer = 0;
        } else {
            $this->buffer .= $data;
        }
        
        $this->length = strlen($this->buffer);
    }
}
