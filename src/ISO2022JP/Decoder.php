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

namespace Opis\Encoding\ISO2022JP;

use Opis\Encoding\Index;
use Opis\Encoding\HandleInterface;

class Decoder implements HandleInterface
{
    const DECODER_ASCII = 0;
    const DECODER_ROMAN = 1;
    const DECODER_KATAKANA = 2;
    const DECODER_LEAD_BYTE = 3;
    const DECODER_TRAIL_BYTE = 4;
    const DECODER_ESCAPE_START = 5;
    const DECODER_ESCAPE = 6;

    protected $index;
    protected $state = self::DECODER_ASCII;
    protected $outputState = self::DECODER_ASCII;
    protected $lead = 0x00;
    protected $output = false;

    public function handle($byte, $stream, &$result)
    {
        switch ($this->state) {
            case self::DECODER_ASCII:
                if ($byte >= 0x00 && $byte <= 0x7F) {
                    switch ($byte) {
                        case 0x1B:
                            $this->state = self::DECODER_ESCAPE_START;
                            return self::STATUS_CONTINUE;
                        case 0x0E:
                        case 0X0F:
                            $this->output = false;
                            return self::STATUS_ERROR;
                        default:
                            $this->output = false;
                            $result = $byte;
                            return self::STATUS_TOKEN;
                    }
                }
                $this->output = false;
                return self::STATUS_ERROR;
            case self::DECODER_ROMAN:
                if ($byte >= 0x00 && $byte <= 0x7F) {
                    switch ($byte) {
                        case 0x1B:
                            $this->state = self::DECODER_ESCAPE_START;
                            return self::STATUS_CONTINUE;
                        case 0x5C:
                            $this->output = false;
                            $result = 0x00A5;
                            return self::STATUS_TOKEN;
                        case 0x7E:
                            $this->output = false;
                            $result = 0x203E;
                            return self::STATUS_TOKEN;
                        case 0x0E:
                        case 0x0F:
                            $this->output = false;
                            return self::STATUS_ERROR;
                        default:
                            $this->output = false;
                            return self::STATUS_TOKEN;
                    }
                }
                $this->output = false;
                return self::STATUS_ERROR;
            case self::DECODER_KATAKANA:
                if ($byte === 0x1B) {
                    $this->state = self::DECODER_ESCAPE_START;
                    return self::STATUS_CONTINUE;
                }
                if ($byte >= 0x21 && $byte <= 0x5F) {
                    $this->output = false;
                    $result = 0xFF61 + $byte - 0x21;
                    return self::STATUS_TOKEN;
                }
                $this->output = false;
                return self::STATUS_ERROR;
            case self::DECODER_LEAD_BYTE:
                if ($byte === 0x1B) {
                    $this->state = self::DECODER_ESCAPE_START;
                    return self::STATUS_CONTINUE;
                }
                if ($byte >= 0x21 && $byte <= 0x7E) {
                    $this->output = false;
                    $this->lead = $byte;
                    $this->state = self::DECODER_TRAIL_BYTE;
                    return self::STATUS_CONTINUE;
                }
                $this->output = false;
                return self::STATUS_ERROR;
            case self::DECODER_TRAIL_BYTE:
                if ($byte === 0x1B) {
                    $this->state = self::DECODER_ESCAPE_START;
                    return self::STATUS_ERROR;
                }
                if ($byte >= 0x21 && $byte <= 0x7E) {
                    $this->state = self::DECODER_LEAD_BYTE;
                    $pointer = ($lead - 0x21) * 94 + $byte - 0x21;
                    if ($this->index === null) {
                        $this->index = Index::get()->jis0208CodePoint();
                    }
                    $cp = isset($this->index[$pointer]) ? $this->index[$pointer] : null;
                    if ($cp === null) {
                        return self::STATUS_ERROR;
                    }
                    $result = $cp;
                    return self::STATUS_TOKEN;
                }
                $this->state = self::DECODER_LEAD_BYTE;
                return self::STATUS_ERROR;
                break;
            case self::DECODER_ESCAPE_START:
                if ($byte === 0x24 || $byte === 0x28) {
                    $this->state = self::DECODER_ESCAPE;
                    return self::STATUS_CONTINUE;
                }
                $stream(chr($byte));
                $this->output = false;
                $this->state = $this->outputState;
                return self::STATUS_ERROR;
            case self::DECODER_ESCAPE:
                $lead = $this->lead;
                $this->lead = 0x00;
                $state = null;
                if ($lead === 0x28 && $byte === 0x42) {
                    $state = self::DECODER_ASCII;
                } elseif ($lead === 0x28 && $byte === 0x4A) {
                    $state = self::DECODER_ROMAN;
                } elseif ($lead === 0x28 && $byte === 0x49) {
                    $state = self::DECODER_KATAKANA;
                } elseif ($lead === 0x24 && ($byte === 0x40 || $byte === 0x42)) {
                    $state = self::DECODER_LEAD_BYTE;
                }

                if ($state !== null) {
                    $this->state = $this->outputState = $state;
                    $output = $this->output;
                    $this->output = true;
                    return $output ? self::STATUS_ERROR : self::STATUS_CONTINUE;
                }

                $stream(chr($lead) . chr($byte));
                $this->output = false;
                $this->state = $this->outputState;
                return self::STATUS_ERROR;
        }
    }

    public function handleEOF($stream, &$result)
    {
        if ($this->state === self::DECODER_TRAIL_BYTE) {
            $stream();
            $this->state = self::DECODER_LEAD_BYTE;
            return self::STATUS_ERROR;
        }
        return self::STATUS_FINISHED;
    }
}
