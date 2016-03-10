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

class Decoder implements HandleInterface
{
    protected $index;
    protected $lead = 0x00;

    public function __construct($index)
    {
        $this->index = $index;
    }

    public function handle($byte, $stream, &$result)
    {
        if ($this->lead !== 0x00) {
            $lead = $this->lead;
            $pointer = null;
            $this->lead = 0x00;
            $offset = $byte < 0x7F ? 0x40 : 0x62;

            if (($byte >= 0x40 && $byte <= 0x7E) || ($byte >= 0xA1 && $byte <= 0xFE)) {
                $pointer = ($lead - 0x81) * 157 + ($byte - $offset);
            }

            switch ($pointer) {
                case 1133:
                    $result = array(0x00CA, 0x0304);
                    return self::STATUS_TOKEN_ARRAY;
                case 1135:
                    $result = array(0x00CA, 0x030C);
                    return self::STATUS_TOKEN_ARRAY;
                case 1164:
                    $result = array(0x00EA, 0x0304);
                    return self::STATUS_TOKEN_ARRAY;
                case 1166:
                    $result = array(0x00EA, 0x030C);
                    return self::STATUS_TOKEN_ARRAY;
            }

            if ($pointer === null) {
                $cp = null;
            } else {
                $cp = isset($this->index[$pointer]) ? $this->index[$pointer] : null;
            }

            if ($cp === null) {
                if ($byte >= 0x00 && $byte <= 0x7F) {
                    $stream(chr($byte));
                }
                return self::STATUS_ERROR;
            }

            $result = $cp;
            return self::STATUS_TOKEN;
        }

        if ($byte >= 0x00 && $byte <= 0x7F) {
            $result = $byte;
            return self::STATUS_TOKEN;
        }

        if ($byte >= 0x81 && $byte <= 0xFE) {
            $this->lead = $byte;
            return self::STATUS_CONTINUE;
        }

        return self::STATUS_ERROR;
    }

    public function handleEOF($stream, &$result)
    {
        if ($this->lead !== 0x00) {
            $this->lead = 0x00;
            return self::STATUS_ERROR;
        }
        return self::STATUS_FINISHED;
    }
}
