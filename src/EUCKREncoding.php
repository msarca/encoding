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

use Opis\Encoding\EUCKR\Encoder;
use Opis\Encoding\EUCKR\Decoder;

class EUCKREncoding extends Encoding
{

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
        return 'EUC-KR';
    }

    protected function getIndexCodePoint()
    {
        static $index = null;

        if ($index === null) {
            $path = dirname(__DIR__) . '/bin/index/euc-kr.php';
            $index = include $path;
        }

        return $index;
    }

    protected function getIndexPointer()
    {
        static $index = null;

        if ($index === null) {
            $value = array();
            foreach ($this->getIndexCodePoint() as $pointer => $codePoint) {
                if (!isset($value[$codePoint])) {
                    $value[$codePoint] = array();
                }
                $value[$codePoint][] = $pointer;
            }
            $index = $value;
        }

        return $index;
    }
}
