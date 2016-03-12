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

class Index
{
    protected static $instance;
    protected $singleByteCP = array();
    protected $singleByteIP = array();
    protected $big5CP;
    protected $big5IP;
    protected $jis0208CP;
    protected $jis0208IP;
    protected $jis0212CP;
    protected $euckrCP;
    protected $euckrIP;
    protected $gb18030CP;
    protected $gb18030IP;
    protected $gb18030Ranges;
    protected $shiftJISIP;

    protected function __construct()
    {
        
    }

    /**
     * 
     * @return  Index
     */
    public static function get()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function singleByteCodePoint($name)
    {
        if (!isset($this->singleByteCP[$name])) {
            $path = dirname(__DIR__) . '/bin/index/' . strtolower($name) . '.php';
            $this->singleByteCP[$name] = include $path;
        }

        return $this->singleByteCP[$name];
    }

    public function singleBytePointer($name)
    {
        if (!isset($this->singleByteIP[$name])) {
            $value = array();
            foreach ($this->singleByteCodePoint($name) as $pointer => $codePoint) {
                if (!isset($value[$codePoint])) {
                    $value[$codePoint] = array();
                }
                $value[$codePoint][] = $pointer;
            }
            $this->singleByteIP[$name] = $value;
        }

        return $this->singleByteIP[$name];
    }

    public function big5CodePoint()
    {
        if ($this->big5CP === null) {
            $path = dirname(__DIR__) . '/bin/index/big5.php';
            $this->big5CP = include $path;
        }

        return $this->big5CP;
    }

    public function big5IndexPointer()
    {
        if ($this->big5IP === null) {
            $value = array();
            $cmp = (0xA1 - 0x81) * 157;
            foreach ($this->big5CodePoint() as $pointer => $codePoint) {
                if ($pointer < $cmp) {
                    continue;
                }
                if (!isset($value[$codePoint])) {
                    $value[$codePoint] = array();
                }
                $value[$codePoint][] = $pointer;
            }
            $this->big5IP = $value;
        }

        return $this->big5IP;
    }

    public function jis0208CodePoint()
    {
        if ($this->jis0208CP === null) {
            $path = dirname(__DIR__) . '/bin/index/jis0208.php';
            $this->jis0208CP = include $path;
        }

        return $this->jis0208CP;
    }

    public function jsi0208IndexPointer()
    {

        if ($this->jis0208IP === null) {
            $value = array();
            foreach ($this->jis0208CodePoint() as $pointer => $codePoint) {
                if (!isset($value[$codePoint])) {
                    $value[$codePoint] = array();
                }
                $value[$codePoint][] = $pointer;
            }
            $this->jis0208IP = $value;
        }

        return $this->jis0208IP;
    }

    public function jis0212CodePoint()
    {
        if ($this->jis0212CP === null) {
            $path = dirname(__DIR__) . '/bin/index/jis0212.php';
            $this->jis0212CP = include $path;
        }

        return $this->jis0212CP;
    }

    public function euckrCodePoint()
    {

        if ($this->euckrCP === null) {
            $path = dirname(__DIR__) . '/bin/index/euc-kr.php';
            $this->euckrCP = include $path;
        }

        return $this->euckrCP;
    }

    public function euckrIndexPointer()
    {
        if ($this->euckrIP === null) {
            $value = array();
            foreach ($this->euckrCodePoint() as $pointer => $codePoint) {
                if (!isset($value[$codePoint])) {
                    $value[$codePoint] = array();
                }
                $value[$codePoint][] = $pointer;
            }
            $this->euckrIP = $value;
        }

        return $this->euckrIP;
    }

    public function gb18030CodePoint()
    {
        if ($this->gb18030CP === null) {
            $path = dirname(__DIR__) . '/bin/index/gb18030.php';
            $this->gb18030CP = include $path;
        }

        return $this->gb18030CP;
    }

    public function gb18030IndexPointer()
    {

        if ($this->gb18030IP === null) {
            $value = array();
            foreach ($this->gb18030CodePoint() as $pointer => $codePoint) {
                if (!isset($value[$codePoint])) {
                    $value[$codePoint] = array();
                }
                $value[$codePoint][] = $pointer;
            }
            $this->gb18030IP = $value;
        }

        return $this->gb18030IP;
    }

    public function gb18030Ranges()
    {
        if ($this->gb18030Ranges === null) {
            $path = dirname(__DIR__) . '/bin/index/gb18030-ranges.php';
            $this->gb18030Ranges = include $path;
        }

        return $this->gb18030Ranges;
    }

    public function shiftJISIndexPointer()
    {
        if ($this->shiftJISIP === null) {
            $value = array();
            foreach ($this->jis0208CodePoint() as $pointer => $codePoint) {
                if ($pointer >= 8272 && $pointer <= 8835) {
                    continue;
                }
                if (!isset($value[$codePoint])) {
                    $value[$codePoint] = array();
                }
                $value[$codePoint][] = $pointer;
            }
            $this->shiftJISIP = $value;
        }

        return $this->shiftJISIP;
    }
}
