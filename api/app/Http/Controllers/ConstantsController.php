<?php

namespace App\Http\Controllers;

use App\Enums\CheckInterval;
use App\Enums\HttpMethod;
use App\Services\Telegram\TelegramApiService;
use Illuminate\Http\JsonResponse;

class ConstantsController extends Controller
{
    public function __construct(
        private readonly TelegramApiService $telegram,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'http_methods' => $this->stringEnumChoices(HttpMethod::cases()),
            'check_intervals' => $this->intEnumChoices(CheckInterval::cases()),
            'telegram_bot_name' => $this->telegram->botName(),
        ]);
    }

    /**
     * @param  array<\BackedEnum>  $cases
     * @return list<array{id: string, name: string}>
     */
    private function stringEnumChoices(array $cases): array
    {
        return array_map(
            fn (\BackedEnum $case) => [
                'id' => (string) $case->value,
                'name' => (string) $case->value,
            ],
            $cases
        );
    }

    /**
     * @param  array<\BackedEnum>  $cases
     * @return list<array{id: int, name: string}>
     */
    private function intEnumChoices(array $cases): array
    {
        return array_map(
            fn (\BackedEnum $case) => [
                'id' => $case->value,
                'name' => (string) $case->value,
            ],
            $cases
        );
    }
}
