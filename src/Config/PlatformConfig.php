<?php
namespace Mooti\Platform\Config;
    
use Mooti\Platform\Util\FileSystem;
use Mooti\Framework\Framework;
use Mooti\Framework\ServiceProvider;
use Mooti\Framework\Exception\DataValidationException;
use Mooti\Validator\Validator;
use Mooti\Framework\Config\AbstractConfig;

class PlatformConfig extends AbstractConfig
{
    const FILENAME = 'platform.json';

    protected $rules = [
        'platform' => [
            'required' => true,
            'type'     => 'object',
            'properties' => [
                'version' => [
                    'required' => 'true',
                    'type'     => 'string',
                    'constraints' => [
                        'length' => [5,null]
                    ]
                ]
            ]
        ],
        'config' => [
            'required' => true,
            'type'     => 'object',
            'properties' => [
                'domain' => [
                    'required' => 'true',
                    'type'     => 'string'
                ]
            ]
        ],
        'repositories' => [
            'required' => true,
            'type'     => 'array',
            'items'    => [
                '*' => [
                    'type'       => 'object',
                    'properties' => [
                        'name' => [
                            'required' => true,
                            'type'     => 'string'
                        ],
                        'url' => [
                            'required' => true,
                            'type'     => 'string'
                        ]
                    ]
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
            'platform' => [
                'version' => ''
            ],
            'config' => [ 
                'domain'    => 'dev.local'
            ],
            'repositories' => []
        ];
    }

    public function setPlatformVersion($version)
    {
        if (isset($this->configData) == false) {
            throw new DataValidationException('Platform config has not been initialised');
        }
        $this->configData['platform']['version'] =  $version;
    }

    public function addRepository($name, $url)
    {
        if (isset($this->configData) == false) {
            throw new DataValidationException('Platform config has not been initialised');
        }

        foreach ($this->configData['repositories'] as $repository) {
            if ($name == $repository['name']) {
                throw new DataValidationException('That repository already exists');
            }
        }
        $this->configData['repositories'][] = [
            'name' => $name,
            'url'  => $url
        ];
    }

    public function removeRepository($name)
    {
        if (isset($this->configData) == false) {
            throw new DataValidationException('Platform config has not been initialised');
        }

        $newRepositoryList = [];
        foreach ($this->configData['repositories'] as $repository) {
            if ($name != $repository['name']) {
                $newRepositoryList[] = $repository;
            }
        }
        $this->configData['repositories'] = $newRepositoryList;
    }
}
