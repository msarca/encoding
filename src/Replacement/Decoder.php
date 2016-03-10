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

namespace Opis\Encoding\Replacement;

use Opis\Encoding\HandleInterface;

class Decoder implements HandleInterface
{
    protected $replacementErrorReturned = false;

    public function handle($byte, &$result)
    {
        if (!$this->replacementErrorReturned) {
            $this->replacementErrorReturned = true;
            return self::STATUS_ERROR;
        }

        return self::STATUS_FINISHED;
    }

    public function handleEOF(&$result)
    {
        return self::STATUS_FINISHED;
    }
}
