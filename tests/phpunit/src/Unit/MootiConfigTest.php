<?php

namespace Mooti\Test\Unit\System\Base;

use Mooti\Xizlr\Core\ServiceProvider;
use Mooti\System\Base\MootiConfig;
use Mooti\System\Base\Exception\DataValidationException;
use Interop\Container\ContainerInterface;
use GUMP;

class MootiConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function validateConfigInvalidMainDataThrowsDataValidationException()
    {
        $validations = [
            'puppet_config' => 'required',
            'repositories'  => 'required'
        ];

        $config = [
            'puppet_config' => [
                'apache_domain'    => 'test.com',
                'root_db_password' => 'password123'
            ],
            'repositories' => [
                [
                    'name' => '1',
                    'url'  => '1',
                    'type' => '1',
                    'alias_domains' => '1'
                ]
            ]
        ];

         $config = [
            'name' => '',
            'repositories' => [
                [
                    'alias_domains' => ['name' => 'hello'],
                    'boo' => []
                ],
                [
                    'alias_domains' => ['name' => 'hello'],
                    'boo' => []
                ],
                []
            ]
         ];

        $validator = $this->getMockBuilder(GUMP::class)
            ->disableOriginalConstructor()
            ->getMock();

        $validator->expects(self::once())
            ->method('run')
            ->with(
                self::equalTo($config),
                self::equalTo($validations)
            )->will(self::returnValue(false));

        $validator->expects(self::once())
            ->method('run')
            ->with(
                self::equalTo($config),
                self::equalTo($validations)
            )->will(self::returnValue(false));


        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container->expects(self::once())
            ->method('get')
            ->with(self::equalTo(ServiceProvider::VALIDATOR))
            ->will(self::returnValue($validator));

        $mootiConfig = new MootiConfig;
        //$mootiConfig->setContainer($container);
        $mootiConfig->validateConfig($config);
    }
}
