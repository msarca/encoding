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

namespace Opis\Encoding\EUCJP;

use Opis\Encoding\HandleInterface;

class Encoder implements HandleInterface
{
    protected $index;

    public function __construct($index)
    {
        $this->index = $index;
    }

    public function handle($codepoint, $stream, &$result)
    {
        if ($codepoint >= 0x0000 && $codepoint <= 0x007F) {
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
            $result = chr(0x8E) . chr($codepoint - 0xFF61 + 0xA1);
            return self::STATUS_TOKEN;
        }

        if ($codepoint === 0x2212) {
            $codepoint = 0xFF0D;
        }

        $pointer = isset($this->index[$codepoint]) ? reset($this->index[$codepoint]) : null;
        $lead = floor($pointer / 94) + 0xA1;
        $trail = $pointer % 94 + 0xA1;
        $result = chr($lead) . chr($trail);

        return self::STATUS_TOKEN;
    }

    public function handleEOF($stream, &$result)
    {
        return self::STATUS_FINISHED;
    }
}
