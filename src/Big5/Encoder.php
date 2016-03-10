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

namespace Opis\Encoding\Big5;

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

        switch ($codepoint) {
            case 0x2550:
            case 0x255E:
            case 0x2561:
            case 0x256A:
            case 0x5341:
            case 0x5345:
                $pointer = isset($this->index[$codepoint]) ? end($this->index[$codepoint]) : null;
                break;
            default:
                $pointer = isset($this->index[$codepoint]) ? reset($this->index[$codepoint]) : null;
        }

        if ($pointer === null) {
            $result = $codepoint;
            return self::STATUS_ERROR;
        }
        $lead = floor($pointer / 157) + 0x81;
        $trail = $pointer % 157;
        $offset = $trail < 0x3F ? 0x40 : 0x62;

        $result = chr($lead) . chr($trail + $offset);
        return self::STATUS_TOKEN;
    }

    public function handleEOF($stream, &$result)
    {
        return self::STATUS_FINISHED;
    }
}
