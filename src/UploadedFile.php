<?php

namespace Frankie\UploadedFile;

use Frankie\SizeParser\SizeParser;
use OutOfBoundsException;

class UploadedFile implements UploadedFileInterface
{
    private const NAME = 'name';
    private const TYPE = 'type';
    private const TMP_NAME = 'tmp_name';
    private const ERROR = 'error';
    private const SIZE = 'size';
    protected string $baseName;
    protected string $mimeType;
    protected string $tmpName;
    protected int $error;
    protected int $size;
    protected string $sizeString;

    public function __construct(array $file, SizeParser $parser)
    {
        $keys = [
            self::NAME,
            self::TYPE,
            self::TMP_NAME,
            self::ERROR,
            self::SIZE
        ];
        foreach ($keys as $key) {
            if (!isset($file[$key])) {
                throw new OutOfBoundsException("Invalid file format. Doesn't have key $key.");
            }
        }
        $this->baseName = $file[self::NAME];
        $this->mimeType = $file[self::TYPE];
        $this->tmpName = $file[self::TMP_NAME];
        $this->error = $file[self::ERROR];
        $this->size = $file[self::SIZE];
        $this->sizeString = $parser->setSize($this->size)
            ->parse()
            ->getParsed();
    }

    /**
     * @param string $path
     * @param int $mode
     * @param string|null $name
     *
     * @return bool
     * @throws FileException
     */
    public function moveTo(string $path, int $mode = 0755, ?string $name = null): bool
    {
        if (!$this->isValid()) {
            throw new FileException("This isn't correct uploaded file.");
        }
        $path = rtrim(
            str_replace(
                [
                    '/',
                    '\\'
                ],
                DIRECTORY_SEPARATOR,
                $path
            ),
            DIRECTORY_SEPARATOR
        );
        if (!file_exists($this->tmpName)) {
            throw new FileException("{$this->tmpName} doesn't exists.");
        }
        if (!is_uploaded_file($this->tmpName)) {
            throw new FileException("{$this->tmpName} isn't uploaded file.");
        }
        if (move_uploaded_file($this->tmpName, $path . DIRECTORY_SEPARATOR . $name)) {
            return chmod($path . DIRECTORY_SEPARATOR . $name ?? $this->baseName, $mode);
        }
        return false;
    }

    public function isValid(): bool
    {
        return $this->error === 1;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getBaseName(): string
    {
        return $this->baseName;
    }

    public function getMediaType(): string
    {
        return $this->mimeType;
    }

    public function getError(): int
    {
        return $this->error;
    }

    /**
     * @return string
     * @throws FileException
     */
    public function getContent(): string
    {
        if (!file_exists($this->tmpName)) {
            throw new FileException("{$this->tmpName} doesn't exists.");
        }
        return file_get_contents($this->tmpName);
    }

    public function getParsedSize(): string
    {
        return $this->sizeString;
    }

    public function getExtension(): string
    {
        return pathinfo($this->baseName, PATHINFO_EXTENSION);
    }

    public function getName(): string
    {
        return pathinfo($this->baseName, PATHINFO_FILENAME);
    }

    public function hasExtension(): bool
    {
        return pathinfo($this->baseName, PATHINFO_EXTENSION) !== '';
    }

    public function hasName(): bool
    {
        return pathinfo($this->baseName, PATHINFO_FILENAME) !== '';
    }
}
