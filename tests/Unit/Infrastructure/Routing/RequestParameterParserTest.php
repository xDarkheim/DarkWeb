<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Routing;

use Darkheim\Infrastructure\Routing\RequestParameterParser;
use PHPUnit\Framework\TestCase;
use Tests\Stubs\ArrayQueryStore;

final class RequestParameterParserTest extends TestCase
{
    private RequestParameterParser $parser;

    protected function setUp(): void
    {
        $this->parser = new RequestParameterParser();
    }

    public function testParseIntoDoesNothingWithoutRequestKey(): void
    {
        $query = new ArrayQueryStore(['page' => 'home']);

        $this->parser->parseInto($query);

        $this->assertSame('home', $query->get('page'));
        $this->assertFalse($query->has('request'));
    }

    public function testParseIntoMapsKeyValuePairs(): void
    {
        $query = new ArrayQueryStore(['request' => 'module/login/user/demo']);

        $this->parser->parseInto($query);

        $this->assertSame('login', $query->get('module'));
        $this->assertSame('demo', $query->get('user'));
    }

    public function testParseIntoSetsNullForMissingOrEmptyValue(): void
    {
        $query = new ArrayQueryStore(['request' => 'foo/bar/baz/']);

        $this->parser->parseInto($query);

        $this->assertSame('bar', $query->get('foo'));
        $this->assertNull($query->get('baz'));
    }

    public function testParseIntoEscapesHtmlLikeLegacyHandler(): void
    {
        $query = new ArrayQueryStore(['request' => 'name/<b>bob</b']);

        $this->parser->parseInto($query);

        $this->assertSame('&lt;b&gt;bob&lt;', $query->get('name'));
    }
}
