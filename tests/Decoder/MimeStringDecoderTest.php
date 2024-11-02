<?php

use Myail\Decoder\MimeStringDecoder;
use PHPUnit\Framework\TestCase;

final class MimeStringDecoderTest extends TestCase
{
    private $mime_string_decoder;

    protected function setUp()
    {
        $this->mime_string_decoder = new MimeStringDecoder();
    }

    public function test_mime_string_decoder_1()
    {
        $input = '=?utf-8?Q?=E6=B7=B1=E5=9C=B3=E5=B8=82=E9=B8=BF=E8=88=AA=E5=9B=BD=E9=99=85=E8=B4=A7=E8=BF=90=E4=BB=A3=E7=90=86=EF=BC=88=E9=A6=99=E6=B8=AF=EF=BC=89=E6=9C=89=E9=99=90=E5=85=AC=E5=8F=B8?=';
        $ideal_output = '深圳市鸿航国际货运代理（香港）有限公司';
        $this->assertSame(
            $ideal_output,
            $this->mime_string_decoder->decode($input)
        );
    }

    public function test_address_list_parser_2()
    {
        $input = '=?utf-8?B?RVhISUJJVElPTiBDQVJHTyBIQU5ETElORy9Qcm9mZXNzaW9uYWwvSGFuZHkgTG9naXN0aWNz?=';
        $ideal_output = 'EXHIBITION CARGO HANDLING/Professional/Handy Logistics';
        $this->assertSame(
            $ideal_output,
            $this->mime_string_decoder->decode($input)
        );
    }
}
