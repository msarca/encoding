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

namespace Opis\Encoding\SingleByte;

use Opis\Encoding\HandleInterface;

class Encoder implements HandleInterface
{
    protected $index;

    public function __construct($index)
    {
        $this->index = $index;
    }

    public function handle($codepoint, &$result)
    {
        if ($codepoint >= 0x0000 && $codepoint < + 0x007F) {
            $result = chr($codepoint);
            return self::STATUS_TOKEN;
        }
        
        $ptr = isset($this->index[$codepoint]) ? reset($this->index[$codepoint]) : null;
        if ($ptr === null) {
            return self::STATUS_ERROR;
        }
        $result = chr($ptr + 0x80);
        return self::STATUS_TOKEN;
    }

    public function handleEOF(&$result)
    {
        return self::STATUS_FINISHED;
    }
}