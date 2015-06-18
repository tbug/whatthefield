<?php

namespace WhatTheField\Node;

class Token
{

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function __toString()
    {
        return sprintf('<%s(%s)>', get_class($this), $this->data);
    }
}

class TokenEQ extends Token
{

}
class TokenAttrBegin extends Token
{

}
class TokenAttrEnd extends Token
{

}
class TokenString extends Token
{

}
class TokenEOD extends Token
{

}




class QueryParser
{
    const S_BEGIN = 1;
    const S_KEY = 2;
    const S_KEYEQ = 3;
    const S_EQ = 4;
    const S_VALUE = 5;
    const S_ATTRBEGIN = 6;
    const S_ATTRKEY = 7;
    const S_ATTRKEYEQ= 8;
    const S_ATTREQ= 9;
    const S_ATTRVALUE = 10;
    const S_ATTREND = 11;

    protected $query;
    protected $state;

    protected function getTokenMap()
    {
        return [
            '/^=/' => TokenEQ::class,
            '/^\[/' => TokenAttrBegin::class,
            '/^\]/' => TokenAttrEnd::class,
            '/^\s*(\w+)\s*/' => TokenString::class,
        ];
    }

    public function __construct($query)
    {
        $this->state = self::S_BEGIN;
        $this->query = $query;
        $this->remaining = $query;
        $this->tokenMap = $this->getTokenMap();
        $this->tokenStack = [];
    }

    protected function peakToken()
    {
        $token = $this->nextToken(true);
        array_push($this->tokenStack, $token);
        return $token;
    }

    protected function nextToken($force=false)
    {
        if (!$force) {
            if (count($this->tokenStack) > 0) {
                return array_pop($this->tokenStack);
            }
        }
        if (strlen($this->remaining) === 0) {
            return null;
        }

        $tokenMap = $this->tokenMap;
        $match = false;
        foreach ($tokenMap as $pattern => $class) {
            $match = preg_match(
                $pattern,
                $this->remaining,
                $matches
            );
            if ($match === 1) {
                break;
            }
        }

        if ($match === false) {
            throw new QueryParserException('ERROR');
        } elseif ($match === 0) {
            throw new QueryParserException(sprintf(
                'Parser error in %s near: %s',
                $this->query,
                $this->remaining
            ));
        }
        $value = $matches[0];
        $movement = strlen($value);
        if (count($matches) > 1) {
            $value = $matches[count($matches)-1];
        }
        $token = new $class($value);
        // move pointer
        $this->remaining = substr($this->remaining, $movement);
        
        return $token;
    }

    public function operations()
    {
        while($token = $this->nextToken()) {
            $next = $this->peakToken();
            // base case, we can handle
            // - TokenString (key match (maybe more))
            // - TokenEQ (value only match)
            // - TokenAttrBegin (attribute lookups)
            if ($token instanceof TokenString) {
                // if the next token is NOT EQ, this should just be key match
                if ($next instanceof TokenEQ) {
                    // also value match
                    $keyToken = $token;
                    $eq = $this->nextToken();
                    $next = $this->peakToken();
                    if ($next instanceof TokenString) {
                        $valueToken = $this->nextToken();
                        yield ['findByKeyAndValue', [$keyToken->data, $valueToken->data]];
                    } else {
                        $this->panic($token);
                    }

                } else {
                    // just key match
                    yield ['findByKey', [$token->data]];
                }
            } elseif ($token instanceof TokenAttrBegin) {
                while($token = $this->nextToken()) {
                    if ($token instanceof TokenAttrEnd) {
                        break;
                    }
                    $next = $this->peakToken();
                    // start matching attribute stuff
                    // two cases here, either EQ or TokenString
                    if ($token instanceof TokenEQ) {
                        // Just a value match
                        if ($next instanceof TokenString) {
                            $valueToken = $this->nextToken();
                            yield ['findByAttribute', [null, $valueToken->data]];
                        } else {
                            $this->panic($token);
                        }
                    } elseif ($token instanceof TokenString) {
                        // Key match, maybe more
                        if ($next instanceof TokenEQ) {
                            // key=value match
                            $keyToken = $token;
                            $eq = $this->nextToken();
                            $next = $this->peakToken();
                            if ($next instanceof TokenString) {
                                $valueToken = $this->nextToken();
                                yield ['findByAttribute', [$keyToken->data, $valueToken->data]];
                            } else {
                                $this->panic($token);
                            }
                        } else {
                            // just key
                            yield ['findByAttribute', [$token->data, null]];
                        }
                    }
                }
            } else {
                $this->panic($token);
            }
        }
    }

    protected function panic($token)
    {
        throw new QueryParserException(sprintf(
            'panic! state: %s, current token: %s, query: %s',
            $this->state,
            $token,
            $this->query
        ));
    }

}

