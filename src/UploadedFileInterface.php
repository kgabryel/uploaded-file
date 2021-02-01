<?php

namespace Frankie\UploadedFile;

interface UploadedFileInterface
{
    public function moveTo(string $path, int $mode = 0755, string $name = null): bool;

    public function getSize(): int;

    public function getBaseName(): string;

    public function getMediaType(): string;

    public function isValid(): bool;

    public function getError(): int;

    public function getContent(): string;

    public function getParsedSize(): string;

    public function getExtension(): string;

    public function getName(): string;

    public function hasExtension(): bool;

    public function hasName(): bool;
}
