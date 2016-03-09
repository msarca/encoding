<?php
/* ===========================================================================
 * Opis Project
 * http://opis.io
 * ===========================================================================
 * Copyright 2016 Marius Sarca
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================================ */

namespace Opis\Encoding;

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
