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

use Opis\Encoding\SingleByte\Encoder;
use Opis\Encoding\SingleByte\Decoder;

class SingleByteEncoding extends Encoding
{
    protected $name;

    protected function __construct($name)
    {
        $this->name = $name;
    }

    public function getDecoder()
    {
        return new Decoder($this->getIndexCodePoint());
    }

    public function getEncoder()
    {
        return new Encoder($this->getIndexPointer());
    }

    public function getName()
    {
        return $this->name;
    }

    protected function getIndexCodePoint()
    {
        static $index = array();

        if (!isset($index[$this->name])) {
            $path = dirname(__DIR__) . '/bin/index/' . strtolower($this->name) . '.php';
            $index[$this->name] = include $path;
        }

        return $index[$this->name];
    }

    protected function getIndexPointer()
    {
        static $index = array();
        if (!isset($index[$this->name])) {
            $value = array();
            foreach ($this->getIndexCodePoint() as $pointer => $codePoint) {
                if (!isset($value[$codePoint])) {
                    $value[$codePoint] = array();
                }
                $value[$codePoint][] = $pointer;
            }
            $index[$this->name] = $value;
        }

        return $index[$this->name];
    }
}
