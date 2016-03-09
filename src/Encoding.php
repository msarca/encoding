<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Opis\Encoding;

/**
 * Description of Encoding
 *
 * @author cylex
 */
abstract class Encoding
{
    
    public static function getEncoding($lablel)
    {
        switch (strtolower($lablel)) {
            case 'utf-8':
            case 'utf8':
            case 'unicode-1-1-utf-8':
                return new UTF8Encoding();
        }
        
        return null;
    }
    
    abstract public function getDecoder();
    
    abstract public function getEncoder();
}
