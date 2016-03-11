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

use Opis\Encoding\Index;
use Opis\Encoding\HandleInterface;

class Decoder implements HandleInterface
{
    protected $name;
    protected $index;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function handle($byte, $stream, &$result)
    {
        if ($byte >= 0x00 && $byte <= 0x7F) {
            $result = $byte;
            return self::STATUS_TOKEN;
        }

        if ($this->index === null) {
            $this->index = Index::get()->singleByteCodePoint($this->name);
        }
        
        $ptr = $byte - 0x80;
        $cp = isset($this->index[$ptr]) ? $this->index[$ptr] : null;
        if ($cp === null) {
            return self::STATUS_ERROR;
        }
        $result = $cp;
        return self::STATUS_TOKEN;
    }

    public function handleEOF($stream, &$result)
    {
        return self::STATUS_FINISHED;
    }
}
