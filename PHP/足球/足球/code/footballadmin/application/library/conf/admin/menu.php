<?php

return [
    'dashboard' => [
        'name' => '控制台',
        'icon' => 'fa-dashboard',
        'list' => [
            [
                'name' => '我的控制台',
                'icon' => 'fa-dashboard',
                'list' => [
                    [
                        'name' => '后台首页',
                        'url' => ['index','dashboard'],
                    ],
                    [
                        'name' => '更新数据缓存',
                        'url' => ['common','refresh'],
                        'class' => 'j-dialog-handle',
                    ],
                    
                ]
            ],
            [
                'name' => '常用模块',
                'icon' => 'fa-dashboard',
                'list' => [
                    [
                    'name' => '常用模块',
                    'url' => ['index','hotmenu'],
                    ],
                 ],
            ],
        ],
    ],

    'cogs' => [
        'name' => '系统',
        'icon' => 'fa-cog',
        'list' => [
            [
                'name' => '系统管理',
                'ename'=> 'cogs_0',
                'icon' => 'fa-cogs',
                'list' => [
                    [
                        'name' => '系统设置',
                        'ename'=> 'cogs_0_0',
                        'url' => ['config','basic'],
                    ],
                    [
                        'name' => '高级设置',
                        'ename'=> 'cogs_0_2',
                        'url' => ['config','system'],
                    ],
                    /*[
                        'name' => '房间机器人',
                        'ename'=> 'cogs_0_3',
                        'url' => ['config','arena_android'],
                    ],*/
                    [
                        'name' => '房间自动结算',
                        'ename'=> 'cogs_0_4',
                        'url' => ['config','arena_auto'],
                    ],
                    [
                        'name' => '请求域名',
                        'ename'=> 'cogs_0_5',
                        'url' => ['config','domain'],
                    ],
                ],
            ],
            [
                'name' => '角色权限',
                'ename'=> 'cogs_2',
                'icon' => 'fa-group',
                'list' => [
                    [
                        'name' => '后台权限点',
                        'ename'=> 'cogs_2_0',
                        'url' => ['Permit','index'],
                    ],
                    [
                        'name' => '角色管理',
                        'ename'=> 'cogs_2_1',
                        'url' => ['role','index'],
                    ],
                    [
                        'name' => '管理员管理',
                        'ename'=> 'cogs_2_2',
                        'url' => ['manager','index'],
                    ]
                ]
            ],
            [
                'name'  => '系统统计',
                'ename'=> 'cogs_5',
                'icon'  => 'fa-bar-chart',
                'list'  => [
                    [
                        'name'  => '系统收支',
                        'ename'=> 'cogs_5_1',
                        'url'   => ['stat.system','income'],
                    ],
                    [
                        'name'  => '房间统计',
                        'ename'=> 'cogs_5_2',
                        'url'   => ['stat.basic','arena'],
                    ],
                ],
            ],
            [
                'name' => '日志',
                'ename'=> 'cogs_6',
                'icon' => 'fa-book',
                'list' => [
                    [
                        'name' => '系统日志',
                        'ename'=> 'cogs_6_0',
                        'url' => ['Log','index']
                    ]
                ]
            ],
        ],
    ],

    'user' => [
        'name' => '会员',
        'icon' => 'fa-group',
        'list'  => [
            [
                'name' => '会员管理',
                'ename'=> 'user_0',
                'icon' => 'fa-group',
                'list' => [
                    [
                        'name' => '会员管理',
                        'ename'=> 'user_0_0',
                        'url' => ['User','index']
                    ],
                    [
                        'name' => '帐户明细',
                        'ename'=> 'user_0_3',
                        'url' => ['User','memberLog'],
                        'siblings' => ['memberlog_sys'],
                    ]
                ],
            ],
        ],
    ],
    'content' => [
        'name' => '内容',
        'icon' => 'fa-tasks',
        'list'  => [
            [
                'name' => '模块',
                'ename'=> 'content_2',
                'icon' => 'fa-th',
                'list' => [
                    [
                        'name' => '竞技模块',
                        'ename'=> 'content_2_0',
                        'url' => ['Layout','sports'],
                        'siblings' => ['sports_add']
                    ],
                ]
            ],
            [
                'name' => '帮助内容',
                'ename'=> 'content_3',
                'icon' => 'fa-th',
                'list' => [
                    /*[
                        'name' => '帮助分类',
                        'ename'=> 'content_3_0',
                        'url' => ['article','help_type'],
                        'siblings' => ['help_type_add']
                    ],*/
                    [
                        'name' => '帮助列表',
                        'ename'=> 'content_3_0',
                        'url' => ['article','help'],
                        'siblings' => ['help_add']
                    ],
                    [
                        'name' => '消息列表',
                        'ename'=> 'content_3_1',
                        'url' => ['article','msg'],
                        'siblings' => ['msg_add']
                    ],
                ]
            ],
        ],
    ],
    'divider_1' => ['name' => 'divider'],
    'football' => [
        'name' => '足球',
        'icon' => 'sport-icon football-icon',
        'list'  => [
            [
                'name' => '房间管理',
                'ename'=> 'football_0',
                'icon' => 'fa-bullseye',
                'list' => [
                    [
                        'name' => '比赛',
                        'ename'=> 'football_0_0',
                        'url' => ['items.football','play']
                    ],
                    [
                        'name' => '房间',
                        'ename'=> 'football_0_1',
                        'url' => ['items.football','arena_list'],
                        'siblings' => ['arena_info'],
                    ],
                    [
                        'name' => '投注',
                        'ename'=> 'football_0_2',
                        'url' => ['items.football','betting_list']
                    ],
                    [
                        'name' => '玩法',
                        'ename'=> 'football_0_3',
                        'url' => ['items.football','rules']
                    ],
                    [
                        'name' => '赛事管理',
                        'ename'=> 'football_0_4',
                        'url' => ['items.football','match']
                    ],
                    [
                        'name' => '球队管理',
                        'ename'=> 'football_0_5',
                        'url' => ['items.football','teams']
                    ],
                ],
            ],
        ],
    ],

];