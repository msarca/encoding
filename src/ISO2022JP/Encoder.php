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

namespace Opis\Encoding\ISO2022JP;

use Opis\Encoding\Index;
use Opis\Encoding\HandleInterface;

class Encoder implements HandleInterface
{
    const ENCODER_ASCII = 0;
    const ENCODER_ROMAN = 1;
    const ENCODER_JIS0208 = 2;

    protected $index;
    protected $state = self::ENCODER_ASCII;

    public function handle($codepoint, $stream, &$result)
    {

        if ($this->state === self::ENCODER_ASCII || $this->state === self::ENCODER_ROMAN) {
            if ($codepoint === 0x000E || $codepoint === 0x000F || $codepoint === 0x001B) {
                $result = 0xFFFD;
                return self::STATUS_ERROR;
            }
        }

        if ($this->state === self::ENCODER_ASCII && $codepoint >= 0x0000 && $codepoint <= 0x007F) {
            $result = chr($codepoint);
            return self::STATUS_TOKEN;
        }

        if ($this->state === self::ENCODER_ROMAN) {
            if ($codepoint >= 0x0000 && $codepoint <= 0x007F && $codepoint !== 0x005C && $codepoint !== 0x007E) {
                $result = chr($codepoint);
                return self::STATUS_TOKEN;
            } elseif ($codepoint === 0x00A5) {
                $result = chr(0x5C);
                return self::STATUS_TOKEN;
            } elseif ($codepoint === 0x203E) {
                $result = chr(0x7E);
                return self::STATUS_TOKEN;
            }
        }

        if ($this->state !== self::ENCODER_ASCII && $codepoint >= 0x0000 && $codepoint <= 0x007F) {
            $stream($codepoint);
            $this->state = self::ENCODER_ASCII;
            $result = chr(0x1B) . chr(0x28) . chr(0x42);
            return self::STATUS_TOKEN;
        }

        if ($this->state !== self::ENCODER_ROMAN && ($codepoint === 0x00A5 || $codepoint === 0x203E)) {
            $stream($codepoint);
            $this->state = self::ENCODER_ROMAN;
            $result = chr(0x1B) . chr(0x28) . chr(0x4A);
            return self::STATUS_TOKEN;
        }

        if ($codepoint === 0x2212) {
            $codepoint = 0xFF0D;
        }

        if ($this->index === null) {
            $this->index = Index::get()->jsi0208IndexPointer();
        }

        $pointer = isset($this->index[$codepoint]) ? reset($this->index[$codepoint]) : null;

        if ($pointer === null) {
            $result = $codepoint;
            return self::STATUS_ERROR;
        }

        if ($this->state !== self::ENCODER_JIS0208) {
            $stream($codepoint);
            $this->state = self::ENCODER_JIS0208;
            $result = chr(0x1B) . chr(0x28) . chr(0x42);
            return self::STATUS_TOKEN;
        }

        $lead = floor($pointer / 94) + 0x21;
        $trail = $pointer % 94 + 0x21;
        $result = chr($lead) . chr($trail);
        return self::STATUS_TOKEN;
    }

    public function handleEOF($stream, &$result)
    {
        if ($this->state !== self::ENCODER_ASCII) {
            $stream();
            $this->state = self::ENCODER_ASCII;
            $result = chr(0x1B) . chr(0x28) . chr(0x42);
            return self::STATUS_TOKEN;
        }
        return self::STATUS_FINISHED;
    }
}
