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

class TextEncoder
{
    protected $encoding;
    protected $errorMode;

    public function __construct(Encoding $encoding, $htmlMode = false)
    {
        $this->encoding = $encoding;
        $this->errorMode = $htmlMode ? 'html' : 'fatal';
    }

    public static function create($label = 'utf-8')
    {
        $encoding = Encoding::getEncoding($label);

        if ($encoding === null || !in_array(strtolower($encoding->getName()), array('utf-8', 'utf-16', 'utf-16be', 'utf-16le'))) {
            throw new Exception('Unsupported encoding ' . $label);
        }

        return new static($encoding);
    }

    public function encode(array $input = array(), $string = false)
    {
        $output = array();
        $encoder = $this->encoding->getEncoder();
        $ptr = 0;
        $length = count($input);

        $stream = function($value, $prepend = true) use(&$input, &$ptr, &$length) {
            if ($prepend) {
                array_splice($input, $ptr + 1, null, $value);
            } else {
                array_splice($input, $length + 1, null, $value);
            }
            $length += is_array($value) ? count($input) : 1;
        };

        while (true) {
            if ($ptr < $length) {
                $codepoint = $input[$ptr];
            } elseif ($ptr === $length) {
                $codepoint = false;
                $stream = function() use(&$ptr) {
                    $ptr--;
                };
            } else {
                break;
            }
            $ptr++;
            $result = null;

            if ($codepoint === false) {
                $status = $encoder->handleEOF($stream, $result);
            } else {
                $status = $encoder->handle($codepoint, $stream, $result);
            }

            if ($status === HandleInterface::STATUS_TOKEN) {
                $output[] = $result;
                continue;
            }

            if ($status === HandleInterface::STATUS_CONTINUE) {
                continue;
            }

            if ($status === HandleInterface::STATUS_ERROR) {
                if ($this->errorMode === 'fatal' || $ptr > $length) {
                    throw new Exception('Error while decoding');
                }

                //html mode here
                if ($result === null) {
                    continue;
                }

                $cp = (string) $result;
                $insert = array(0x0026, 0x0023);
                for ($j = 0, $jl = strlen($cp); $j < $jl; $j++) {
                    $insert[] = ord($cp[$j]);
                }
                $insert[] = 0x003B;
                $stream($insert);
                continue;
            }

            if ($status === HandleInterface::STATUS_FINISHED) {
                return $string ? implode('', $output) : $output;
            }
        }
    }

    public function encoding()
    {
        return strtolower($this->encoding->getName());
    }
}
