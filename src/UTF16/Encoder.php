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

class Encoder implements HandleInterface
{
    protected $beEncoder = false;

    protected function __construct($beencoder = false)
    {
        $this->beEncoder = $beencoder;
    }

    public function handle($codepoint, &$result)
    {
        if ($codepoint >= 0x0000 && $codepoint <= 0xFFFF) {
            $byte1 = $codepoint >> 8;
            $byte2 = $codepoint & 0x00FF;
            if ($this->beEncoder) {
                $result = chr($byte1) . chr($byte2);
            } else {
                $result = chr($byte2) . chr($byte1);
            }
            return self::STATUS_TOKEN;
        }

        $result = '';
        $lead = (($codepoint - 0x10000) >> 10) + 0xD800;
        $trail = (($codepoint - 0x10000) & 0x3FF) + 0xDC00;

        foreach (array($lead, $trail) as $item) {
            $byte1 = $item >> 8;
            $byte2 = $item & 0x00FF;
            if ($this->beEncoder) {
                $result .= chr($byte1) . chr($byte2);
            } else {
                $result .= chr($byte2) . chr($byte1);
            }
        }
        return self::STATUS_TOKEN;
    }

    public function handleEOF(&$result)
    {
        return self::STATUS_FINISHED;
    }
}
