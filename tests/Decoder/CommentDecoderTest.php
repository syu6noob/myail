<?php

namespace Decoder;

use Myail\Decoder\CommentDecoder;
use PHPUnit\Framework\TestCase;

final class CommentDecoderTest extends TestCase
{
    private $comment_decoder;

    protected function setUp()
    {
        $this->comment_decoder = new CommentDecoder();
    }

    public function test_comment_decoder_1()
    {
        $input = 'Pete(A nice \) chap) <pete(his account)@silly.test(his host)>';
        $ideal_output = 'Pete <pete@silly.test>';
        $this->assertSame(
            $ideal_output,
            $this->comment_decoder->decode($input)
        );
    }
}
