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

abstract class Encoding
{

    protected function __construct()
    {
        // Protected constructor
    }

    abstract public function getDecoder();

    abstract public function getEncoder();

    abstract public function getName();

    public static function getEncoding($label)
    {
        static $instances = array();

        $encoding = static::getEncodingName($label);

        switch ($encoding) {
            //The Encoding
            case 'UTF-8':
                if (!isset($instances[$encoding])) {
                    $instances[$encoding] = new UTF8Encoding();
                }
                return $instances[$encoding];
            //Legacy single-byte encodings
            case 'IBM866':
            case 'ISO-8859-2':
            case 'ISO-8859-3':
            case 'ISO-8859-4':
            case 'ISO-8859-5':
            case 'ISO-8859-6':
            case 'ISO-8859-7':
            case 'ISO-8859-8':
            case 'ISO-8859-8-i':
            case 'ISO-8859-10':
            case 'ISO-8859-13':
            case 'ISO-8859-14':
            case 'ISO-8859-15':
            case 'ISO-8859-16':
            case 'KOI8-R':
            case 'KOI8-U':
            case 'macintosh':
            case 'windows-874':
            case 'windows-1250':
            case 'windows-1251':
            case 'windows-1252':
            case 'windows-1253':
            case 'windows-1254':
            case 'windows-1255':
            case 'windows-1256':
            case 'windows-1257':
            case 'windows-1258':
            case 'x-mac-cyrillic':
                if (!isset($instances[$encoding])) {
                    $instances[$encoding] = new SingleByteEncoding($encoding);
                }
                return $instances[$encoding];
            //Legacy multi-byte Chinese (simplified) encodings
            case 'GBK':
            case 'gb18030':
                if (!isset($instances[$encoding])) {
                    $instances[$encoding] = new GBEncoding($encoding);
                }
                return $instances[$encoding];
            //Legacy multi-byte Chinese (traditional) encodings
            case 'Big5':
                return new Big5Encoding();
            //Legacy multi-byte Japanese encodings
            case 'EUC-JP':
                return new EUCJPEncoding();
            case 'ISO-2022-JP':
                return new ISO2022JPEncoding();
            case 'Shift_JIS':
                return new ShiftJISEncoding();
            //Legacy multi-byte Korean encodings
            case 'EUC-KR':
                return new EUCKREncoding();
            //Legacy miscellaneous encodings
            case 'replacement':
                if (!isset($instances[$encoding])) {
                    $instances[$encoding] = new ReplacementEncoding();
                }
                return $instances[$encoding];
            case 'UTF-16BE':
            case 'UTF-16LE':
                if (!isset($instances[$encoding])) {
                    $instances[$encoding] = new UTF16Encoding($encoding);
                }
                return $instances[$encoding];
            case 'x-user-defined':
                if (!isset($instances[$encoding])) {
                    $instances[$encoding] = new UserDefined();
                }
                return $instances[$encoding];
        }

        return null;
    }

    public static function getEncodingName($label)
    {
        switch (strtolower(trim($label))) {
            //The Encoding
            case 'unicode-1-1-utf-8':
            case 'utf-8':
            case 'utf8':
                return 'UTF-8';
            //Legacy single-byte encodings
            case '866':
            case 'cp866':
            case 'csibm866':
            case 'ibm866':
                return 'IBM866';
            case 'csisolatin2':
            case 'iso-8859-2':
            case 'iso-ir-101':
            case 'iso8859-2':
            case 'iso88592':
            case 'iso_8859-2':
            case 'iso_8859-2:1987':
            case 'l2':
            case 'latin2':
                return 'ISO-8859-2';
            case 'csisolatin3':
            case 'iso-8859-3':
            case 'iso-ir-109':
            case 'iso8859-3':
            case 'iso88593':
            case 'iso_8859-3':
            case 'iso_8859-3:1988':
            case 'l3':
            case 'latin3':
                return 'ISO-8859-3';
            case 'csisolatin4':
            case 'iso-8859-4':
            case 'iso-ir-110':
            case 'iso8859-4':
            case 'iso88594':
            case 'iso_8859-4':
            case 'iso_8859-4:1988':
            case 'l4':
            case 'latin4':
                return 'ISO-8859-4';
            case 'csisolatincyrillic':
            case 'cyrillic':
            case 'iso-8859-5':
            case 'iso-ir-144':
            case 'iso8859-5':
            case 'iso88595':
            case 'iso_8859-5':
            case 'iso_8859-5:1988':
                return 'ISO-8859-5';
            case 'arabic':
            case 'asmo-708':
            case 'csiso88596e':
            case 'csiso88596i':
            case 'csisolatinarabic':
            case 'ecma-114':
            case 'iso-8859-6':
            case 'iso-8859-6-e':
            case 'iso-8859-6-i':
            case 'iso-ir-127':
            case 'iso8859-6':
            case 'iso88596':
            case 'iso_8859-6':
            case 'iso_8859-6:1987':
                return 'ISO-8859-6';
            case 'csisolatingreek':
            case 'ecma-118':
            case 'elot_928':
            case 'greek':
            case 'greek8':
            case 'iso-8859-7':
            case 'iso-ir-126':
            case 'iso8859-7':
            case 'iso88597':
            case 'iso_8859-7':
            case 'iso_8859-7:1987':
            case 'sun_eu_greek':
                return 'ISO-8859-7';
            case 'csiso88598e':
            case 'csisolatinhebrew':
            case 'hebrew':
            case 'iso-8859-8':
            case 'iso-8859-8-e':
            case 'iso-ir-138':
            case 'iso8859-8':
            case 'iso88598':
            case 'iso_8859-8':
            case 'iso_8859-8:1988':
            case 'visual':
                return 'ISO-8859-8';
            case 'csiso88598i':
            case 'iso-8859-8-i':
            case 'logical':
                return 'ISO-8859-8-i';
            case 'csisolatin6':
            case 'iso-8859-10':
            case 'iso-ir-157':
            case 'iso8859-10':
            case 'iso885910':
            case 'l6':
            case 'latin6':
                return 'ISO-8859-10';
            case 'iso-8859-13':
            case 'iso8859-13':
            case 'iso885913':
                return 'ISO-8859-13';
            case 'iso-8859-14':
            case 'iso8859-14':
            case 'iso885914':
                return 'ISO-8859-14';
            case 'csisolatin9':
            case 'iso-8859-15':
            case 'iso8859-15':
            case 'iso885915':
            case 'iso_8859-15':
            case 'l9':
                return 'ISO-8859-15';
            case 'iso-8859-16':
                return 'ISO-8859-16';
            case 'cskoi8r':
            case 'koi':
            case 'koi8':
            case 'koi8-r':
            case 'koi8_r':
                return 'KOI8-R';
            case 'koi8-ru':
            case 'koi8-u':
                return 'KOI8-U';
            case 'csmacintosh':
            case 'mac':
            case 'macintosh':
            case 'x-mac-roman':
                return 'macintosh';
            case 'dos-874':
            case 'iso-8859-11':
            case 'iso8859-11':
            case 'iso885911':
            case 'tis-620':
            case 'windows-874':
                return 'windows-874';
            case 'cp1250':
            case 'windows-1250':
            case 'x-cp1250':
                return 'windows-1250';
            case 'cp1251':
            case 'windows-1251':
            case 'x-cp1251':
                return 'windows-1251';
            case 'ansi_x3.4-1968':
            case 'ascii':
            case 'cp1252':
            case 'cp819':
            case 'csisolatin1':
            case 'ibm819':
            case 'iso-8859-1':
            case 'iso-ir-100':
            case 'iso8859-1':
            case 'iso88591':
            case 'iso_8859-1':
            case 'iso_8859-1:1987':
            case 'l1':
            case 'latin1':
            case 'us-ascii':
            case 'windows-1252':
            case 'x-cp1252':
                return 'windows-1252';
            case 'cp1253':
            case 'windows-1253':
            case 'x-cp1253':
                return 'windows-1253';
            case 'cp1254':
            case 'csisolatin5':
            case 'iso-8859-9':
            case 'iso-ir-148':
            case 'iso8859-9':
            case 'iso88599':
            case 'iso_8859-9':
            case 'iso_8859-9:1989':
            case 'l5':
            case 'latin5':
            case 'windows-1254':
            case 'x-cp1254':
                return 'windows-1254';
            case 'cp1255':
            case 'windows-1255':
            case 'x-cp1255':
                return 'windows-1255';
            case 'cp1256':
            case 'windows-1256':
            case 'x-cp1256':
                return 'windows-1256';
            case 'cp1257':
            case 'windows-1257':
            case 'x-cp1257':
                return 'windows-1257';
            case 'cp1258':
            case 'windows-1258':
            case 'x-cp1258':
                return 'windows-1258';
            case 'x-mac-cyrillic':
            case 'x-mac-ukrainian':
                return 'x-mac-cyrillic';
            //Legacy multi-byte Chinese (simplified) encodings
            case 'chinese':
            case 'csgb2312':
            case 'csiso58gb231280':
            case 'gb2312':
            case 'gb_2312':
            case 'gb_2312-80':
            case 'gbk':
            case 'iso-ir-58':
            case 'x-gbk':
                return 'GBK';
            case 'gb18030':
                return 'gb18030';
            //Legacy multi-byte Chinese (traditional) encodings
            case 'big5':
            case 'big5-hkscs':
            case 'cn-big5':
            case 'csbig5':
            case 'x-x-big5':
                return 'Big5';
            //Legacy multi-byte Japanese encodings
            case 'cseucpkdfmtjapanese':
            case 'euc-jp':
            case 'x-euc-jp':
                return 'EUC-JP';
            case 'csiso2022jp':
            case 'iso-2022-jp':
                return 'ISO-2022-JP';
            case 'csshiftjis':
            case 'ms932':
            case 'ms_kanji':
            case 'shift-jis':
            case 'shift_jis':
            case 'sjis':
            case 'windows-31j':
            case 'x-sjis':
                return 'Shift_JIS';
            //Legacy multi-byte Korean encodings
            case 'cseuckr':
            case 'csksc56011987':
            case 'euc-kr':
            case 'iso-ir-149':
            case 'korean':
            case 'ks_c_5601-1987':
            case 'ks_c_5601-1989':
            case 'ksc5601':
            case 'ksc_5601':
            case 'windows-949':
                return 'EUC-KR';
            //Legacy miscellaneous encodings
            case 'csiso2022kr':
            case 'hz-gb-2312':
            case 'iso-2022-cn':
            case 'iso-2022-cn-ext':
            case 'iso-2022-kr':
                return 'replacement';
            case 'utf-16be':
                return 'UTF-16BE';
            case 'utf16':
            case 'utf-16':
            case 'utf-16le':
                return 'UTF-16LE';
            case 'x-user-defined':
                return 'x-user-defined';
        }

        return null;
    }
}
