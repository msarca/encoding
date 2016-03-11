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

class Decoder implements HandleInterface
{
    protected $jis0212 = false;
    protected $lead = 0x00;
    protected $index;
    protected $indexJIS0212;

    public function __construct($index, $indexJIS0212)
    {
        $this->index = $index;
        $this->index = $indexJIS0212;
    }

    public function handle($byte, $stream, &$result)
    {
        if ($this->lead === 0x8E && ($byte >= 0xA1 && $byte <= 0xDF)) {
            $this->lead = 0x00;
            $result = 0xFF61 + $byte - 0xA1;
            return self::STATUS_TOKEN;
        }

        if ($this->lead === 0x8F && ($byte >= 0xA1 && $byte <= 0xFE)) {
            $this->jis0212 = true;
            $this->lead = $byte;
            return self::STATUS_CONTINUE;
        }

        if ($this->lead !== 0x00) {
            $lead = $this->lead;
            $this->lead = 0x00;
            $cp = null;
            if (($lead >= 0xA1 && $lead <= 0xFE) && ($byte >= 0xA1 && $byte <= 0xFE)) {
                $pointer = ($lead - 0xA1) * 94 + $byte - 0xA1;
                $index = $this->jis0212 ? $this->indexJIS0212 : $this->index;
                $cp = isset($index[$pointer]) ? $index[$pointer] : null;
            }
            $this->jis0212 = false;
            if (!($byte >= 0xA1 && $byte <= 0xFE)) {
                $stream(chr($byte));
            }
            if ($cp === null) {
                return self::STATUS_ERROR;
            }
            $result = $cp;
            return self::STATUS_TOKEN;
        }

        if ($byte >= 0x00 && $byte <= 0x7F) {
            $result = $byte;
            return self::STATUS_TOKEN;
        }

        if ($byte === 0x8E || $byte === 0x8F || ($byte >= 0xA1 && $byte <= 0xFE)) {
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
