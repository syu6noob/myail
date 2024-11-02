<?php

namespace Decoder;

use Myail\Decoder\QuotedPrintableStringDecoder;
use PHPUnit\Framework\TestCase;

final class QuotedPrintableStringDecoderTest extends TestCase
{
    private $quoted_printable_string_decoder;

    protected function setUp()
    {
        $this->quoted_printable_string_decoder = new QuotedPrintableStringDecoder();
    }

    public function test_mime_string_decoder_1()
    {
        $input = 'Cha=C3=AEne de test';
        $ideal_output = 'Chaîne de test';
        $this->assertSame(
            $ideal_output,
            $this->quoted_printable_string_decoder->decode($input)
        );
    }
}
