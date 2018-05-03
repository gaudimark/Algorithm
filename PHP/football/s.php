{
    "name": "phpoffice/phpexcel",
    "description": "PHPExcel - OpenXML - Read, Create and Write Spreadsheet documents in PHP - Spreadsheet engine",
    "type": "library",
    "keywords": ["OpenXML", "excel", "php", "spreadsheet", "xls", "xlsx"],
    "homepage": "http://phpexcel.codeplex.com",
    "license": "LGPL",
    "authors": [
        {
            "name": "Maarten Balliauw",
            "homepage": "http://blog.maartenballiauw.be"
        },
        {
        "name": "Mark Baker"
        },
        {
        "name": "Franck Lefevre",
        "homepage": "http://blog.rootslabs.net"
        },
        {
        "name": "Erik Tilt"
        }
    ],
    "require": {
        "ext-xml": "*",
        "ext-xmlwriter": "*",
        "php": ">=5.2.0"
    },
    "require-dev": {},
    "autoload": {
        "psr-0": {
            "PHPExcel": "Classes/"
        }
    }
},
{
"name": "topthink/think-captcha",
"description": "captcha package for thinkphp5""type": "library",
"keywords": ["OpenXML", "excel", "php", "spreadsheet", "xls", "xlsx"],
"homepage": "http://phpexcel.codeplex.com",
"license": "Apache-2.0",
"authors": ["name": "yunwuxin", "email": "448901948@qq.com"],
"require": {},
"require-dev": {},
"autoload": {
"psr-4": {
"think\\captcha\\": "src/"
},
"files": ["src/helper.php"]
}
},

{
"name": "workerman/workerman",
"description": "An asynchronous event driven PHP framework for easily building fast, scalable network applications.",
"type": "library",
"keywords": ["asynchronous", "event-loop"],
"homepage": "http://www.workerman.net",
"license": "MIT",
"authors": [{
"name": "walkor",
"email": "walkor@workerman.net",
"homepage": "http://www.workerman.net",
"role": "Developer"
}],
"require": {},
"require-dev": {},
"autoload": {
"psr-4": {
"Workerman\\": "./"
}
}
},

{
"name": "topthink/think-worker",
"description": "workerman extend for thinkphp5""type": "library",
"keywords": ["asynchronous", "event-loop"],
"license": ["Apache-2.0"],
"authors": [{
"name": "liu21st",
"email": "liu21st@gmail.com"
}],
"require": {},
"require-dev": {},
"autoload": {
"psr-4": {
"Workerman\\": "./"
}
}
}