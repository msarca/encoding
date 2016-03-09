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

namespace Opis\Encoding;

class Stream
{
    const EOF = null;

    protected $buffer;
    protected $pointer;
    protected $length;

    public function __construct($buffer = '')
    {
        $this->buffer = $buffer;
        $this->length = strlen($buffer);
        $this->pointer = 0;
    }

    public function read()
    {
        if ($this->pointer >= $this->length) {
            return static::EOF;
        }

        return $this->buffer[$this->pointer++];
    }

    public function write($data, $prepend = false)
    {
        if ($prepend) {
            $this->buffer = $data . substr($this->buffer, $this->pointer);
            $this->pointer = 0;
        } else {
            $this->buffer .= $data;
        }

        $this->length = strlen($this->buffer);
    }
}
