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
    const DO_NOT_FLUSH = 1;
    const BOM_SEEN = 2;

    protected $encoding;
    protected $ignoreBOM;
    protected $errorMode;
    protected $decoder;
    protected $flags = 0;

    //public function __construct($label = 'utf-8', array $options = array())
    public function __construct(Encoding $encoding, $fatalMode = false, $ignoreBOM = false)
    {
        $this->encoding = $encoding;
        $this->errorMode = $fatalMode ? 'fatal' : 'replacement';
        $this->ignoreBOM = $ignoreBOM;
    }

    public static function create($label = 'utf-8', array $options = array())
    {
        $encoding = Encoding::getEncoding($label);

        if ($encoding === null || strtolower($encoding->getName()) === 'replacement') {
            throw new Exception('Invalid encoding: ' . $label);
        }

        $options += array(
            'fatal'     => false,
            'ignoreBOM' => false,
        );

        $instance = new static($encoding, (bool) $options['fatal'], (bool) $options['ignoreBOM']);

        return $instance;
    }

    public function decode($input = '', array $options = array())
    {
        $options += array(
            'stream' => false,
        );

        if (!($this->flags & self::DO_NOT_FLUSH == self::DO_NOT_FLUSH)) {
            $this->decoder = $this->encoding->getDecoder();
            $this->flags &= ~self::BOM_SEEN;
        }

        if ($options['stream']) {
            $this->flags |= self::DO_NOT_FLUSH;
        } else {
            $this->flags &= ~self::DO_NOT_FLUSH;
        }

        $output = array();

        for ($i = 0, $l = strlen($input); $i < $l; $i++) {
            $result = null; //Reset result
            $byte = ord($input[$i]);
            $status = $this->decoder->handle($byte, $result);

            if ($status === HandleInterface::STATUS_TOKEN) {
                $output[] = $result;
                continue;
            }

            if ($status === HandleInterface::STATUS_CONTINUE) {
                continue;
            }

            if ($status === HandleInterface::STATUS_ERROR) {
                if ($this->errorMode == 'fatal') {
                    throw new Exception('Error while decoding');
                }

                if ($result !== null) {
                    $length = strlen($result);

                    if ($length === 1) {
                        if ($input[$i] !== $result) {
                            $input[$i] = $result;
                        }
                        $i--;
                    } else {
                        $input = substr($input, 0, $i + 1) . $result . substr($input, $i + 1);
                        $l += $length;
                    }
                }

                //replacement
                $output[] = 0xFFFD;
                continue;
            }

            if ($status === HandleInterface::STATUS_TOKEN_ARRAY) {
                foreach ($result as $value) {
                    $output[] = $result;
                }
                continue;
            }

            if ($status === HandleInterface::STATUS_FINISHED) {
                break;
            }
        }

        if ($this->flags & self::DO_NOT_FLUSH == self::DO_NOT_FLUSH) {
            return $this->serializeStream($output);
        }

        $status = $this->decoder->handleEOF($result);

        if ($status === HandleInterface::STATUS_ERROR) {
            throw new Exception('Error while decoding');
        }

        return $this->serializeStream($output);
    }

    public function encoding()
    {
        return strtolower($this->encoding->getName());
    }

    public function fatal()
    {
        return $this->errorMode === static::FATAL_MODE;
    }

    public function ignoreBOM()
    {
        return $this->ignoreBOM;
    }

    protected function serializeStream(array $stream)
    {
        $matchEncoding = in_array($this->encoding(), array('utf-8', 'utf-16be', 'utf-16le', 'utf-16'));

        if ($matchEncoding && !$this->ignoreBOM && !($this->flags & self::BOM_SEEN == self::BOM_SEEN)) {
            $this->flags |= self::BOM_SEEN;
            if (0xFEFF === reset($stream)) {
                array_shift($stream);
            }
        }

        return $stream;
    }
}
