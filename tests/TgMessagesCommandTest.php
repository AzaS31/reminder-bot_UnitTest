<?php

use App\Commands\TgMessagesCommand;
use App\Application;
use App\Telegram\TelegramApiImpl;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Commands\TgMessagesCommand::run
 * @covers \App\Commands\TgMessagesCommand::__construct
 * @covers \App\Application::__construct
 * @uses \App\Telegram\TelegramApiImpl
 */

class TgMessagesCommandTest extends TestCase
{
    public function testRunCallsGetMessagesAndOutputsJson()
    {
        $appMock = $this->createMock(Application::class);
        $appMock->method('env')->with('TELEGRAM_TOKEN')->willReturn('fake-token');

        $tgApiMock = $this->getMockBuilder(TelegramApiImpl::class)
            ->setConstructorArgs(['fake-token']) 
            ->onlyMethods(['getMessages'])
            ->getMock();

        $expectedMessages = [['id' => 1, 'text' => 'Hello']];
        $tgApiMock->expects($this->once()) 
            ->method('getMessages')
            ->with(0)
            ->willReturn($expectedMessages);

        $command = $this->getMockBuilder(TgMessagesCommand::class)
            ->setConstructorArgs([$appMock]) 
            ->onlyMethods(['run'])
            ->getMock();

        $command->expects($this->once())->method('run')->willReturnCallback(function () use ($tgApiMock) {
            echo json_encode($tgApiMock->getMessages(0));
        });

        ob_start();
        $command->run();
        $output = ob_get_clean();

        $this->assertEquals(json_encode($expectedMessages), $output);
    }
}
