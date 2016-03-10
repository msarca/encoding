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

namespace Opis\Encoding\UTF16;

use Opis\Encoding\HandleInterface;

class Decoder implements HandleInterface
{
    protected $leadByte = null;
    protected $leadSurrogate = null;
    protected $beDecoder = false;
    
    protected function __construct($bedecoder = false)
    {
        $this->beDecoder = $bedecoder;
    }

    public function handle($byte, &$result)
    {
        if ($this->leadByte === null) {
            $this->leadByte = $byte;
            return self::STATUS_CONTINUE;
        }

        if ($this->beDecoder) {
            $cu = ($this->leadByte << 8) + $byte;
        } else {
            $cu = ($byte << 8) + $this->leadByte;
        }

        $this->leadByte = null;

        if ($this->leadSurrogate !== null) {
            $ls = $this->leadSurrogate;
            $this->leadSurrogate = null;
            if ($cu >= 0xDC00 && $cu <= 0xDFFF) {
                $result = 0x10000 + (($ls - 0xD800) << 10) + ($cu - 0xDC00);
                return self::STATUS_TOKEN;
            }
            $byte1 = $cu >> 8;
            $byte2 = $cu & 0x00FF;
            if ($this->beDecoder) {
                $result = chr($byte1) . chr($byte2);
            } else {
                $result = chr($byte2) . chr($byte1);
            }

            return self::STATUS_ERROR;
        }

        if ($cu >= 0xD800 && $cu <= 0xDBFF) {
            $this->leadSurrogate = $cu;
            return self::STATUS_CONTINUE;
        }

        if ($cu >= 0xDC00 && $cu <= 0xDFFF) {
            return self::STATUS_ERROR;
        }
        
        $result = $cu;
        return self::STATUS_TOKEN;
    }

    public function handleEOF(&$result)
    {
        if ($this->leadByte !== null || $this->leadSurrogate !== null) {
            $this->leadByte = $this->leadSurrogate = null;
            return self::STATUS_ERROR;
        }

        return self::STATUS_FINISHED;
    }
}
