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

class Decoder implements HandleInterface
{
    protected $index;
    protected $lead = 0x00;

    public function handle($byte, $stream, &$result)
    {
        if ($this->lead !== 0x00) {
            $lead = $this->lead;
            $pointer = null;
            $this->lead = 0x00;
            $offset = $byte < 0x7F ? 0x40 : 0x41;
            $leadOffset = $byte < 0xA0 ? 0x81 : 0xC1;

            if (($byte >= 0x40 && $byte <= 0x7E) || ($byte >= 0x80 && $byte <= 0xFC)) {
                $pointer = ($lead - $leadOffset) * 188 + $byte - $offset;
            }

            if ($pointer === null) {
                $cp = null;
            } else {
                if ($this->index === null) {
                    $this->index = Index::get()->jis0208CodePoint();
                }
                $cp = isset($this->index[$pointer]) ? $this->index[$pointer] : null;
            }

            if ($cp === null) {
                if ($pointer >= 8836 && $pointer <= 10528) {
                    $result = 0xE000 + $pointer - 8836;
                    return self::STATUS_TOKEN;
                }
                if ($byte >= 0x00 && $byte <= 0x7F) {
                    $stream($byte);
                }
                return self::STATUS_ERROR;
            }

            $result = $cp;
            return self::STATUS_TOKEN;
        }

        if (($byte >= 0x00 && $byte <= 0x7F) || $byte === 0x80) {
            $result = $byte;
            return self::STATUS_TOKEN;
        }
        if ($byte >= 0xA1 && $byte <= 0xDF) {
            $result = 0xFF61 + $byte - 0xA1;
            return self::STATUS_TOKEN;
        }
        if (($byte >= 0x81 && $byte <= 0x9F) || ($byte >= 0xE0 && $byte <= 0xFC)) {
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
