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

class TextEncoder
{
    protected $isDecoder = false;
    protected $encoding;

    public function __construct(Encoding $encoding)
    {
        $this->encoding = $encoding;
    }

    public static function create($label = 'utf-8')
    {
        $encoding = Encoding::getEncoding($label);
        
        if ($encoding === null || !in_array(strtolower($encoding->getName()), array('utf-8', 'utf-16', 'utf-16be', 'utf-16le'))) {
            throw new Exception('Unsupported encoding ' . $label);
        }
        
        return new static($encoding);
    }

    public function encode(array $input = array())
    {
        $output = '';
        $result = null;
        $encoder = $this->encoding->getEncoder();

        for ($i = 0, $l = count($input); $i < $l; $i++) {
            $codepoint = $input[$i];
            $status = $encoder->handle($codepoint, $result);

            if ($status === HandleInterface::STATUS_TOKEN) {
                $output .= $result;
                continue;
            }

            if ($status === HandleInterface::STATUS_ERROR) {
                //Error mode fatal
                throw new Exception('Error while decoding');
            }
        }
        
        return $output;
    }

    public function encoding()
    {
        return strtolower($this->encoding->getName());
    }
}
