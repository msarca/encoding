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

class Decoder implements HandleInterface
{
    protected $cp = 0;
    protected $seen = 0;
    protected $needed = 0;
    protected $lower = 0x80;
    protected $upper = 0xBF;

    public function handle($byte, &$result)
    {
        if ($this->needed === 0) {
            if ($byte >= 0x00 && $byte <= 0x7F) {
                $result = $byte;
                return self::STATUS_TOKEN;
            } elseif ($byte >= 0xC2 && $byte <= 0XDF) {
                $this->needed = 1;
                $this->cp = $byte - 0xC0;
            } elseif ($byte >= 0xE0 && $byte <= 0xEF) {
                switch ($byte) {
                    case 0xE0:
                        $this->lower = 0xA0;
                        break;
                    case 0xED:
                        $this->upper = 0x9F;
                        break;
                }
                $this->needed = 2;
                $this->cp = $byte - 0xE0;
            } elseif ($byte >= 0xF0 && $byte <= 0xF4) {
                switch ($byte) {
                    case 0xF0:
                        $this->lower = 0x90;
                        break;
                    case 0xF4:
                        $this->upper = 0x8F;
                        break;
                }
                $this->needed = 3;
                $this->cp = $byte - 0xF0;
            } else {
                return self::STATUS_ERROR;
            }

            $this->cp = $this->cp << (6 * $this->needed);
            return self::STATUS_CONTINUE;
        }

        if (!($byte >= $this->lower && $byte <= $this->upper)) {
            $this->cp = $this->needed = $this->seen = 0;
            $this->lower = 0x80;
            $this->upper = 0xBF;
            $result = $token;
            return self::STATUS_ERROR;
        }

        $this->lower = 0x80;
        $this->upper = 0xBF;
        $this->seen++;
        $this->cp += ($byte - 0x80) << (6 * ($this->needed - $this->seen));

        if ($this->needed !== $this->seen) {
            return self::STATUS_CONTINUE;
        }

        $result = $this->cp;
        $this->cp = $this->needed = $this->seen = 0;
        return self::STATUS_TOKEN;
    }

    public function handleEOF(&$result)
    {
        if ($this->needed !== 0) {
            $this->needed = 0;
            return self::STATUS_ERROR;
        }
        return self::STATUS_FINISHED;
    }
}
