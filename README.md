# whatthefield

_Detect structures of collections in XML (and other) documents_

## What

WhatTheField is primarity a library, but a demo CLI exists at `tests/demo/executable.php`
It searches a DOM (feed) for values (nodes) matching a customizable configuration of value types.

WhatTheField takes a score approach to value type discovery, where you express the importance of different features.
The scoring can be compared to the ElasticSearch approach to composable queries.

Here is a short config example:
```php
$isDatetime = new Score\Max([
    new Score\IsDateTimeFormat(DateTime::ISO8601),
    new Score\IsDateTimeFormat(DateTime::ATOM),
    new Score\IsDateTimeFormat(DateTime::RSS),
    new Score\IsDateTimeFormat(DateTime::W3C),
    new Score\IsDateTimeFormat(DateTime::RFC3339),
    new Score\IsDateTimeFormat('Y-m-d H:i:s'),
    new Score\Boost(0.5, [
        new Score\IsDateTimeFormat(DateTime::RFC822),
        new Score\IsDateTimeFormat(DateTime::RFC850),
        new Score\IsDateTimeFormat(DateTime::RFC1036),
        new Score\IsDateTimeFormat(DateTime::RFC1123),
        new Score\IsDateTimeFormat(DateTime::RFC2822),
        new Score\IsDateTimeFormat(DateTime::COOKIE),
    ]),
]);
return [
    'datetime' => $isDatetime,
    'id' => new Score\Sum([
        // is unique
        new Score\IsUnique(),
        // is not (boost of -1 == IS NOT)
        new Score\Boost(-1, [
            // a URL
            new Score\IsFilterVar(FILTER_VALIDATE_URL),
            // a decimal number ("." seperated)
            new Score\IsDecimal(),
        ]),
        // Not a date.
        new Score\Boost(-1, [
            $isDatetime
        ]),
        // // tie breaker on ancestor level 
        new Score\Boost(-0.001, [
            new Score\AncestorCount(),
        ]),
        // // tie breaker, by word count. More words == less likely to be the id
        new Score\Boost(-0.05, [
            new Score\MatchCount('/\s+/S'),
        ]),
        // // tie breaker, not a number
        new Score\Boost(-0.25, [
            new Score\IsMatch('/[^\d]+/S'),
        ]),
    ])
];
```

This config describes two fields to look for:
- datetime
- id

## How

WhatTheField will first look for the primary collection in the provided document.
The primary collection is the outer-most, biggest collection of similar children.
e.g.
```xml
<things>
  <thing><!-- content --></thing>
  <thing><!-- content --></thing>
  <thing><!-- content --></thing>
</things>
```
Here the collection is `<thing>` in `<things>`, or XPath: `/things/thing`.

From the collection of items, WhatTheField searches for fields that best match your config
across the entire feed.
The result is an array where the key is the configuration name (e.g. `datetime` or `id`) and the value
is a sorted array of xpath to score mapping:
```php
$result = [
  'id' => ['/things/thing/datetime' => 2.2, '/things/thing/looks_a_little_like_datetime' => 0.1],  
  /* ... */
]
```
