<?php

namespace Eviweb\Composer\Testing;

use PHPUnit\Framework\TestCase;

class ComposerRunnerTest extends TestCase
{
    private ComposerRunner $composer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->composer = new ComposerRunner();
    }

    public function testRunWithOption()
    {
        $this->assertStringContainsString('Composer version', $this->composer->run('--version'));
    }

    public function testRunWithCommand()
    {
        $this->assertStringContainsString('show', $this->composer->run('list'));
    }

    public function testRunWithCommandAndOptions()
    {
        $json = $this->composer->run('list', '--format=json');
        $array = json_decode($json, true);

        $this->assertArrayHasKey('application', $array);
    }

    public function testSetWorkingDirectory()
    {
        $path = realpath(__DIR__ . '/../fixtures/packages/main');
        $expected = json_decode(file_get_contents($path . '/composer.json'), true)['name'];
        $actual = trim(
            $this->composer
                ->setWorkingDirectory($path)
                ->run('show', '-s', '--name-only')
        );

        $this->assertEquals($expected, $actual);
    }

    public function testGetErrorOutput()
    {
        $expected = 'The "-t" option does not exist.';

        $this->assertStringContainsString($expected, $this->composer->run('-t'));
    }

    public function testSucceedAndFailedBeforeCommandIsRun()
    {
        $this->assertNull($this->composer->succeed());
        $this->assertNull($this->composer->failed());
    }

    public function testSucceedAndFailedOnSuccess()
    {
        $this->composer->run('-V');

        $this->assertTrue($this->composer->succeed());
        $this->assertFalse($this->composer->failed());
    }

    public function testSucceedAndFailedOnFailure()
    {
        $this->composer->run('-t');

        $this->assertFalse($this->composer->succeed());
        $this->assertTrue($this->composer->failed());
    }
}
