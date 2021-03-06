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

use Exception;

class TextDecoder
{
    protected $encoding;
    protected $ignoreBOM;
    protected $errorMode;
    protected $decoder;
    protected $doNotFlush = false;
    protected $bomSeen = false;

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

        if (!$this->doNotFlush) {
            $this->decoder = $this->encoding->getDecoder();
            $this->bomSeen = false;
        }

        $this->doNotFlush = (bool) $options['stream'];

        $ptr = 0;
        $length = strlen($input);
        $output = array();

        $stream = function($value, $prepend = true) use(&$input, &$ptr, &$length) {
            if ($prepend) {
                $input = substr($input, 0, $ptr + 1) . $value . substr($input, $ptr + 1);
            } else {
                $input .= $value;
            }
            $length += strlen($value);
        };

        while (true) {
            // Read from stream
            if ($ptr < $length) {
                $byte = ord($input[$ptr]);
            } elseif ($ptr === $length) {
                if ($this->doNotFlush) {
                    return $this->serializeStream($output);
                }
                $byte = false;
                $stream = function() use(&$ptr) {
                    $ptr--;
                };
            } else {
                break;
            }

            $ptr++;
            $result = null; //Reset result

            if ($byte === false) {
                $status = $this->decoder->handleEOF($stream, $result);
            } else {
                $status = $this->decoder->handle($byte, $stream, $result);
            }

            if ($status === HandleInterface::STATUS_TOKEN) {
                $output[] = $result;
                continue;
            }

            if ($status === HandleInterface::STATUS_CONTINUE) {
                continue;
            }

            if ($status === HandleInterface::STATUS_ERROR) {
                if ($this->errorMode == 'fatal' || $ptr > $length) {
                    throw new Exception('Error while decoding');
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
                return $this->serializeStream($output);
            }
        }
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

        if ($matchEncoding && !$this->ignoreBOM && !$this->bomSeen) {
            $this->bomSeen = true;
            if (0xFEFF === reset($stream)) {
                array_shift($stream);
            }
        }

        return $stream;
    }
}
