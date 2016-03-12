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

namespace Opis\Encoding\ShiftJIS;

use Opis\Encoding\Index;
use Opis\Encoding\HandleInterface;

class Encoder implements HandleInterface
{
    protected $index;

    public function handle($codepoint, $stream, &$result)
    {
        if (($codepoint >= 0x0000 && $codepoint <= 0x007F) || $codepoint === 0x0080) {
            $result = chr($codepoint);
            return self::STATUS_TOKEN;
        }
        if ($codepoint === 0x00A5) {
            $result = chr(0x5C);
            return self::STATUS_TOKEN;
        }
        if ($codepoint === 0x203E) {
            $result = chr(0x7E);
            return self::STATUS_TOKEN;
        }
        if ($codepoint >= 0xFF61 && $codepoint <= 0xFF9F) {
            $result = chr($codepoint - 0xFF61 + 0xA1);
            return self::STATUS_TOKEN;
        }
        if ($codepoint === 0x2212) {
            $codepoint = 0xFF0D;
        }
        if ($this->index === null) {
            $this->index = Index::get()->shiftJISIndexPointer();
        }
        $pointer = isset($this->index[$codepoint]) ? reset($this->index[$codepoint]) : null;
        if ($pointer === null) {
            $result = $codepoint;
            return self::STATUS_ERROR;
        }
        $lead = floor($pointer / 188);
        $leadOffset = $lead < 0x1F ? 0x81 : 0xC1;
        $trail = $pointer % 188;
        $offset = $trail < 0x3F ? 0x40 : 0x41;
        $result = chr($lead + $leadOffset) . chr($trail + $offset);
        return self::STATUS_TOKEN;
    }

    public function handleEOF($stream, &$result)
    {
        return self::STATUS_FINISHED;
    }
}
