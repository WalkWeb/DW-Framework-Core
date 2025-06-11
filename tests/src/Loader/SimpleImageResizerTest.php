<?php

declare(strict_types=1);

namespace Tests\src\Loader;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\AbstractTestCase;
use WalkWeb\NW\AppException;
use WalkWeb\NW\Loader\Image;
use WalkWeb\NW\Loader\LoaderException;
use WalkWeb\NW\Loader\SimpleImageResizer;

class SimpleImageResizerTest extends AbstractTestCase
{
    /**
     * @throws AppException
     */
    #[DataProvider('successDataProvider')]
    public function testSimpleImageResizerSuccess(Image $image): void
    {
        $resizeImagePath = $this->getResizer()->resize($image, 300, 300);

        $path = $this->dir . '/../public/' . $resizeImagePath;

        self::assertFileExists($path);
    }

    /**
     * @throws AppException
     */
    public function testSimpleImageResizerNoResize(): void
    {
        $image = new Image(
            'test image',
            'png',
            39852,
            398,
            261,
            __DIR__ . '/files/01.png',
            'file_path'
        );

        self::assertEquals($image->getFilePath(), $this->getResizer()->resize($image, 500, 300));
    }

    public function testSimpleImageResizerAbsolutePathNotFound(): void
    {
        $this->expectException(AppException::class);
        $this->expectExceptionMessage(LoaderException::ERROR_NO_DIRECTORY);
        $this->getResizer()->resize($this->getImage(), 300, 300, 50, '/invalid_dir/');
    }

    /**
     * @return array
     */
    public static function successDataProvider(): array
    {
        return [
            // jpeg
            [
                new Image(
                    'test image',
                    'jpg',
                    38673,
                    642,
                    666,
                    __DIR__ . '/files/03.jpg',
                    'file_path'
                )
            ],
            // png
            [
                new Image(
                    'test image',
                    'png',
                    138388,
                    642,
                    666,
                    __DIR__ . '/files/04.png',
                    'file_path'
                )
            ],
            // gif
            [
                new Image(
                    'test image',
                    'gif',
                    100700,
                    642,
                    666,
                    __DIR__ . '/files/05.gif',
                    'file_path'
                )
            ],
        ];
    }

    /**
     * @return SimpleImageResizer
     * @throws AppException
     */
    private function getResizer(): SimpleImageResizer
    {
        return new SimpleImageResizer(self::getContainer());
    }

    /**
     * @return Image
     */
    private function getImage(): Image
    {
        $name = 'test image';
        $type = 'jpg';
        $size = 38673;
        $width = 642;
        $height = 666;
        $absoluteDir = __DIR__ . '/files/';
        $absoluteFilePath = $absoluteDir . '03.jpg';
        $filePath = 'file_path';

        return new Image($name, $type, $size, $width, $height, $absoluteFilePath, $filePath);
    }

    private function activeExceptionHandlers(): array
    {
        $res = [];

        while (true) {
            $previousHandler = set_exception_handler(static fn () => null);
            restore_exception_handler();

            if ($previousHandler === null) {
                break;
            }
            $res[] = $previousHandler;
            restore_exception_handler();
        }
        $res = array_reverse($res);

        foreach ($res as $handler) {
            set_exception_handler($handler);
        }

        return $res;
    }

}
