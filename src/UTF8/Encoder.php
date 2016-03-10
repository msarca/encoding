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

namespace Opis\Encoding\UTF8;

use Opis\Encoding\HandleInterface;

class Encoder implements HandleInterface
{

    public function handle($codepoint, $stream, &$result)
    {
        if ($codepoint >= 0x0000 && $codepoint <= 0x007F) {
            $result = chr($codepoint);
            return self::STATUS_TOKEN;
        }

        if ($codepoint >= 0x0080 && $codepoint <= 0x07FF) {
            $count = 1;
            $offset = 0xC0;
        } elseif ($codepoint >= 0x0800 && $codepoint <= 0xFFFF) {
            $count = 2;
            $offset = 0xE0;
        } elseif ($codepoint >= 0x10000 && $codepoint <= 0x10FFFF) {
            $count = 3;
            $offset = 0xF0;
        }

        $bytes = chr(($codepoint >> (6 * $count)) + $offset);

        while ($count > 0) {
            $temp = $codepoint >> (6 * ($count - 1));
            $bytes .= chr(0x80 | ($temp & 0x3F));
            $count--;
        }

        $result = $bytes;

        return self::STATUS_TOKEN;
    }

    public function handleEOF($stream, &$result)
    {
        return self::STATUS_FINISHED;
    }
}
