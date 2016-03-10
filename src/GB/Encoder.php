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

namespace Opis\Encoding\GB;

use Opis\Encoding\HandleInterface;

class Encoder implements HandleInterface
{
    protected $gbk = false;
    protected $index;
    protected $range;

    public function __construct($index, $range, $gbk = false)
    {
        $this->index = $index;
        $this->range = $range;
        $this->gbk = $gbk;
    }

    public function handle($codepoint, $stream, &$result)
    {
        if ($codepoint >= 0x0000 && $codepoint <= 0x007F) {
            $result = chr($codepoint);
            return self::STATUS_TOKEN;
        }

        if ($codepoint === 0xE5E5) {
            $result = $codepoint;
            return self::STATUS_ERROR;
        }

        if ($this->gbk && $codepoint === 0x20AC) {
            $result = 0x80;
            return self::STATUS_TOKEN;
        }

        $pointer = isset($this->index[$codepoint]) ? reset($this->index[$codepoint]) : null;

        if ($pointer !== null) {
            $lead = floor($pointer / 190) + 0x81;
            $trail = $pointer % 190;
            $offset = $pointer < 0x3F ? 0x40 : 0x41;
            $result = chr($lead) . chr($trail + $offset);
            return self::STATUS_TOKEN;
        }

        if ($this->gbk) {
            $result = $codepoint;
            return self::STATUS_ERROR;
        }

        if ($codepoint === 0xE7C7) {
            $pointer = 7457;
        } else {
            $offset = $po = null;
            foreach ($this->range as $ptr => $cpv) {
                if ($cpv <= $codepoint) {
                    $offset = $cpv;
                    $po = $ptr;
                    continue;
                }
                break;
            }
            $pointer = $po + $codepoint - $offset;
        }
        $byte1 = floor($pointer / 12600);
        $pointer = $pointer - $byte1 * 12600;
        $byte2 = floor($pointer / 1260);
        $pointer = $pointer - $byte2 * 1260;
        $byte3 = floor($pointer / 10);
        $byte4 = $pointer - $byte3 * 10;

        $result = chr($byte1 + 0x82) . chr($byte2 + 0x30) . chr($byte3 + 0x81) . chr($byte4 + 0x30);
        return self::STATUS_TOKEN;
    }

    public function handleEOF($stream, &$result)
    {
        return self::STATUS_FINISHED;
    }
}
