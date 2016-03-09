<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Opis\Encoding;

/**
 * Description of TextDecoder
 *
 * @author cylex
 */
class TextDecoder
{
    protected $encoding;
    protected $ignoreBOM = false;
    protected $errorMode = 'replacement';
    protected $flagDoNotFlush = false;
    protected $flagBOMSeen = false;
    protected $decoder;
    protected $stream;

    public function __construct($label = 'utf-8', array $options = array())
    {
        $this->encoding = Encoding::getEncoding($label);
        //TODO: If encoding is failure or replacement, throw a RangeError. 
        $options += array(
            'fatal' => false,
            'ignoreBOM' => false,
        );

        if ($options['fatal']) {
            $this->errorMode = 'fatal';
        }

        if ($options['ignoreBOM']) {
            $this->ignoreBOM = true;
        }
    }

    public function decode($input = '', array $options = array())
    {
        $options += array(
            'stream' => false,
        );

        if (!$this->flagDoNotFlush) {
            $this->decoder = $this->encoding->getDecoder();
            $this->stream = new Stream();
            $this->flagBOMSeen = false;
        }

        $this->flagDoNotFlush = (bool) $options['stream'];

        if ($input !== '') {
            $this->stream->write($input);
        }

        $output = new Stream();

        while (true) {
            $token = $this->stream->read();
            if ($token === Stream::EOF && $this->flagDoNotFlush) {
                return $this->serializeStream($output);
            }
            
            $result = $this->process($token, $this->decoder, $this->stream, $output, $this->errorMode);
            
            if ($result['type'] === 'finished') {
                return $this->serializeStream($output);
            }
            
            if ($result['type'] === 'error') {
                throw new Exception('Error while decoding');
            }
        }
    }

    public function encoding()
    {
        return strtolower($this->encoding->getName());
    }

    public function fatal()
    {
        return $this->errorMode === 'fatal';
    }

    public function ignoreBOM()
    {
        return $this->ignoreBOM;
    }
    
    protected function process($token, $decoder, $stream, $output, $errorMode)
    {
        
    }

    protected function serializeStream($stream)
    {
        
    }
}
