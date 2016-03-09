<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Opis\Encoding;

use Opis\Encoding\UTF8\Encoder;
use Opis\Encoding\UTF8\Decoder;

/**
 * Description of UTF8Encoding
 *
 * @author cylex
 */
class UTF8Encoding extends Encoding
{
    public function getDecoder()
    {
        return new Decoder();
    }

    public function getEncoder()
    {
        return new Encoder();
    }
}
