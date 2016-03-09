<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Opis\Encoding;

/**
 *
 * @author cylex
 */
interface HandleInterface
{
    public function handle($byte, $input, $output, $mode = null);
}
