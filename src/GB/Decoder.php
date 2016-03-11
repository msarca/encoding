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

use Opis\Encoding\Index;
use Opis\Encoding\HandleInterface;

class Decoder implements HandleInterface
{
    protected $first = 0x00;
    protected $second = 0x00;
    protected $third = 0x00;
    protected $index;
    protected $range;

    public function handle($byte, $stream, &$result)
    {
        if ($this->third !== 0x00) {
            $cp = null;
            //index gb18030 ranges code point
            if ($byte >= 0x30 && $byte <= 0x39) {
                $pointer = ((($this->first - 0x81) * 10 + $this->second - 0x30) * 126 + $this->third - 0x81) * 10 + $byte - 0x30;
                if (($pointer > 39419 && $pointer < 189000) || $pointer > 1237575) {
                    $cp = null;
                } elseif ($pointer === 7457) {
                    $cp = 0xE7C7;
                } else {
                    if ($this->range === null) {
                        $this->range = Index::get()->gb18030Ranges();
                    }
                    $offset = null;
                    $cpo = null;
                    foreach ($this->range as $ptr => $cpv) {
                        if ($ptr <= $pointer) {
                            $offset = $ptr;
                            $cpo = $cpv;
                            continue;
                        }
                        break;
                    }
                    $cp = $cpo + $pointer - $offset;
                }
            }

            if ($cp === null) {
                $stream(chr($this->second) . chr($this->third) . chr($byte));
                $this->first = $this->second = $this->third = 0x00;
                return self::STATUS_ERROR;
            }
            $this->first = $this->second = $this->third = 0x00;
            $result = $cp;
            return self::STATUS_TOKEN;
        }

        if ($this->second !== 0x00) {
            if ($byte >= 0x81 && $byte <= 0xFE) {
                $this->third = $byte;
                return self::STATUS_CONTINUE;
            }
            $stream(chr($this->second) . chr($byte));
            $this->first = $this->second = 0x00;
            return self::STATUS_ERROR;
        }

        if ($this->first !== 0x00) {
            if ($byte >= 0x30 && $byte <= 0x39) {
                $this->second = $byte;
                return self::STATUS_CONTINUE;
            }
            $lead = $this->first;
            $pointer = null;
            $this->first = 0x00;
            $offset = $byte < 0x7F ? 0x40 : 0x41;
            if (($byte >= 0x40 && $byte <= 0x7E) || ($byte >= 0x80 && $byte <= 0xFE)) {
                $pointer = ($lead - 0x81) * 190 + ($byte - $offset);
            }

            if ($pointer === null) {
                $cp = null;
            } else {
                if ($this->index === null) {
                    $this->index = Index::get()->gb18030CodePoint();
                }
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

        if ($byte === 0x80) {
            $result = 0x20AC;
            return self::STATUS_TOKEN;
        }

        if ($byte >= 0x81 && $byte <= 0xFE) {
            $this->first = $byte;
            return self::STATUS_CONTINUE;
        }

        return self::STATUS_ERROR;
    }

    public function handleEOF($stream, &$result)
    {
        if ($this->first === 0x00 && $this->second === 0x00 && $this->third === 0x00) {
            return self::STATUS_FINISHED;
        }

        $this->first = $this->second = $this->third = 0x00;
        return self::STATUS_ERROR;
    }
}
