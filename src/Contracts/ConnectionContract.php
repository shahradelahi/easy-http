<?php

namespace EasyHttp\Contracts;

/**
 * ConnectionContract class
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Arthur Kushman (https://github.com/arthurkushman)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 */
interface ConnectionContract
{

	public function send(string $data): void;

	public function close(): void;

	public function getUniqueSocketId(): int;

	public function getPeerName(): string;

	public function broadCast(string $data): void;

	public function broadCastMany(array $data, int $delay): void;

}
