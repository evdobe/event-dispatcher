<?php declare(strict_types=1);

namespace Application\Messaging;

use Application\Event\Store;

interface Handler
{
    public function __construct(Store $store, ?Filter $filter = null, ?Translator $translator = null);
    public function handle(Message $message, string $channel): void;
}
