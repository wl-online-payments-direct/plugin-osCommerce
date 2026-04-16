<?php

declare (strict_types=1);
namespace common\modules\orderPayment\WOP;

// scoper.inc.php
use common\modules\orderPayment\WOP\Isolated\Symfony\Component\Finder\Finder;
$finders = [Finder::create()->files()->ignoreVCS(\true)->ignoreDotFiles(\false)->in('.')];
return [
    'prefix' => null,
    'php-version' => "7.4",
    // string|null
    'finders' => $finders,
    // list<Finder>
    'patchers' => [],
    // list<callable(string $filePath, string $prefix, string $contents): string>
    'exclude-files' => [],
    // list<string>
    'exclude-namespaces' => ['/^OnlinePayments\\\\Sdk/'],
    // list<string|regex>
    'exclude-constants' => [],
    // list<string|regex>
    'exclude-classes' => [],
    // list<string|regex>
    'exclude-functions' => [],
    // list<string|regex>
    'expose-global-constants' => \false,
    // bool
    'expose-global-classes' => \false,
    // bool
    'expose-global-functions' => \false,
    // bool
    'expose-namespaces' => [],
    // list<string|regex>
    'expose-constants' => [],
    // list<string|regex>
    'expose-classes' => [],
    // list<string|regex>
    'expose-functions' => [],
];
