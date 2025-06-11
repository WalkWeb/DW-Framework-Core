<?php

declare(strict_types=1);

namespace WalkWeb\NW\Loader;

use DateTime;
use Exception;
use Gumlet\ImageResize;
use WalkWeb\NW\AppException;
use WalkWeb\NW\Container;
use WalkWeb\NW\Traits\StringTrait;

class SimpleImageResizer
{
    use StringTrait;

    public const string DIRECTORY       = '/public/images/upload/';
    public const string FRONT_DIRECTORY = '/images/upload/';
    public const int QUALITY            = 80;
    public const int NAME_LENGTH        = 10;
    public const string EXTENSION       = '.jpg';

    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param Image $image
     * @param int $maxWidth
     * @param int $maxHeight
     * @param int $quality
     * @param string $directory
     * @return string
     * @throws AppException
     */
    public function resize(
        Image $image,
        int $maxWidth,
        int $maxHeight,
        int $quality = self::QUALITY,
        string $directory = self::DIRECTORY
    ): string {
        try {
            if ($image->getWidth() <= $maxWidth && $image->getHeight() <= $maxHeight) {
                return $image->getFilePath();
            }

            $resizeImage = new ImageResize($image->getAbsoluteFilePath());
            $resizeImage->resizeToHeight($maxWidth);
            $resizeImage->resizeToWidth($maxHeight);
            $resizeImage->quality_jpg = $quality;

            $date = new DateTime();
            $dirSuffix = $date->format('Y') . '/' . $date->format('m') . '/' . $date->format('d') . '/';
            $name = self::generateString(self::NAME_LENGTH) . self::EXTENSION;

            $absoluteDir = $this->container->getRootDir() . $directory . $dirSuffix;
            $absolutePath =  $absoluteDir. $name;
            $path = self::FRONT_DIRECTORY . $dirSuffix . $name;

            if (!file_exists($absoluteDir)) {
                throw new AppException(LoaderException::ERROR_NO_DIRECTORY . $absoluteDir);
            }

            $resizeImage->save($absolutePath);

            return $path;
        } catch (Exception $e) {
            restore_exception_handler();
            throw new AppException($e->getMessage());
        }
    }
}
