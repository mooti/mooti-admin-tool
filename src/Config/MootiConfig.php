<?php
namespace Mooti\Platform\Config;
    
use Mooti\Platform\Util\FileSystem;
use Mooti\Framework\Framework;
use Mooti\Framework\ServiceProvider;
use Mooti\Framework\Exception\DataValidationException;
use Mooti\Validator\Validator;
use Mooti\Framework\Config\AbstractConfig;

class MootiConfig extends AbstractConfig
{
    const FILENAME = 'mooti.json';

    protected $rules = [
        'name' => [
            'required' => true,
            'type'     => 'string'
        ],
        'server' => [
            'required' => false,
            'type'     => 'object',
            'properties' => [
                'type' => [
                    'required' => true,
                    'type'     => 'string'
                ],
                'web_root' => [
                    'required' => true,
                    'type'     => 'string'
                ],
                'index_file' => [
                    'required' => true,
                    'type'     => 'string'
                ]
            ]
        ],
        'scripts' => [
            'required' => false,
            'type'     => 'array',
            'items'    => [
                '*' => [
                    'type' => 'string'
                ]                
            ]
        ]
    ];

    public function __construct()
    {
        $this->filename = self::FILENAME;
    }

    public function init()
    {
        $this->configData = [
            'name' => 'mooti.example'
        ];
    }
}
